<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShortUrlController extends Controller
{
       // Show list of short URLs
       public function index()
       {
           $user = Auth::user();
       
           // SuperAdmin can see all URLs, Admin and Member can only see their company's URLs
           if ($user->role->name === 'SuperAdmin') {
               $urls = ShortUrl::with('user', 'company')->paginate(15);
           } elseif ($user->role->name === 'ClientAdmin' || $user->role->name === 'Admin') {
               $urls = ShortUrl::where('company_id', $user->company_id)->with('user')->paginate(15);
           } else {
               // Members can only see their own URLs
               $urls = ShortUrl::where('user_id', $user->id)->with('company')->paginate(15);
           }

           return view('urls.index', compact('urls'));
       }

       // Show single short URL
       public function show(ShortUrl $shortUrl)
       {
           $user = Auth::user();

           // Check if user is authorized to view this URL
           $this->authorize('view', $shortUrl);

           return view('urls.show', compact('shortUrl'));
       }

       // Show create form
       public function create()
       {
           $user = Auth::user();
       
           // SuperAdmin cannot create short URLs
           if ($user->role->name === 'SuperAdmin') {
               return back()->with('error', 'SuperAdmin cannot create short URLs');
           }

           return view('urls.create');
       }

       // Generate a new URL
       public function store(Request $request)
       {
           $request->validate([
               'original_url' => 'required|url',
           ]);

           $user = Auth::user();
       
           // SuperAdmin cannot create short URLs
           if ($user->role->name === 'SuperAdmin') {
               return back()->with('error', 'SuperAdmin cannot create short URLs');
           }

           // Generate unique short code
           $shortCode = Str::random(6);
           while (ShortUrl::where('short_code', $shortCode)->exists()) {
               $shortCode = Str::random(6);
           }

           $new = ShortUrl::create([
               'user_id' => Auth::id(),
               'company_id' => Auth::user()->company_id,
               'original_url' => $request->original_url,
               'short_code' => $shortCode,
               'clicks' => 0
           ]);

            // Cache the created mapping immediately
            $cacheKey = 'short_url:' . $shortCode;
            $ttl = 60 * 60 * 24 * 30; // 30 days
            Cache::put($cacheKey, [
                'id' => $new->id,
                'original_url' => $new->original_url,
                'is_active' => $new->is_active ?? true,
            ], $ttl);

           return back()->with('success', 'Short URL created successfully');
       }

       // Edit short URL
       public function edit(ShortUrl $shortUrl)
       {
           $user = Auth::user();

           // Check if user is authorized to edit this URL
           $this->authorize('update', $shortUrl);

           return view('urls.edit', compact('shortUrl'));
       }

       // Update short URL
       public function update(Request $request, ShortUrl $shortUrl)
       {
           $user = Auth::user();

           // Check if user is authorized to update this URL
           $this->authorize('update', $shortUrl);

           $request->validate([
               'original_url' => 'required|url',
           ]);

           $shortUrl->update([
               'original_url' => $request->original_url,
           ]);

           // Invalidate cache for this short code
           Cache::forget('short_url:' . $shortUrl->short_code);

           return back()->with('success', 'Short URL updated successfully');
       }

       // Delete short URL
       public function destroy(ShortUrl $shortUrl)
       {
           $user = Auth::user();

           // Check if user is authorized to delete this URL
           $this->authorize('delete', $shortUrl);

           $shortUrl->delete();

           // Remove from cache when deleted
           Cache::forget('short_url:' . $shortUrl->short_code);

           return back()->with('success', 'Short URL deleted successfully');
       }

       // Redirection logic with caching and is_active check
       public function redirect($code)
       {
           $cacheKey = 'short_url:' . $code;
           $ttl = 60 * 60 * 24 * 30; // 30 days

           $cached = Cache::get($cacheKey);

           if ($cached) {
               // If cached entry shows inactive, return 410
               if (isset($cached['is_active']) && !$cached['is_active']) {
                   return response()->view('links.disabled', [], 410);
               }

               // Increment clicks in DB asynchronously (best-effort)
               try {
                   ShortUrl::where('id', $cached['id'])->increment('clicks');
               } catch (\Throwable $e) {
                   // ignore increment failures
               }

               return redirect()->away($cached['original_url']);
           }

           // Cache miss: fetch from DB
           $url = ShortUrl::where('short_code', $code)->firstOrFail();

           // store in cache for future
           Cache::put($cacheKey, [
               'id' => $url->id,
               'original_url' => $url->original_url,
               'is_active' => $url->is_active ?? true,
           ], $ttl);

           if (!$url->is_active) {
               return response()->view('links.disabled', [], 410);
           }

           $url->increment('clicks');

           return redirect()->away($url->original_url);
       }

       // Download CSV
       public function download(Request $request)
       {
           $user = Auth::user();
           $filter = $request->query('filter');

            // build base query depending on role
            if ($user->role->name === 'SuperAdmin') {
                $query = ShortUrl::query();
            } elseif ($user->role->name === 'Member') {
                $query = ShortUrl::where('user_id', $user->id);
            } else {
                $query = ShortUrl::where('company_id', $user->company_id);
            }

            if ($filter === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($filter === 'week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($filter === 'month') {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            }

            $urls = $query->get();

           $response = new StreamedResponse(function () use ($urls) {
               $handle = fopen('php://output', 'w');

               fputcsv($handle, [
                   'Original URL', 'Short URL', 'Clicks', 'Created By'
               ]);

               foreach ($urls as $url) {
                   fputcsv($handle, [
                       $url->original_url,
                       url('/' . $url->short_code),
                       $url->clicks,
                       $url->user->name
                   ]);
               }

               fclose($handle);
           });

           $response->headers->set('Content-Type', 'text/csv');
           $response->headers->set('Content-Disposition', 'attachment; filename="short_urls.csv"');

           return $response;
       }

        /**
         * Toggle is_active status for a short URL (Admin and SuperAdmin)
         */
        public function toggleStatus(Request $request, ShortUrl $shortUrl)
        {
            $user = Auth::user();

            // Only SuperAdmin or Admin for same company can toggle
            if ($user->role->name !== 'SuperAdmin') {
                if (!in_array($user->role->name, ['Admin', 'ClientAdmin']) || $user->company_id !== $shortUrl->company_id) {
                    return back()->with('error', 'Unauthorized');
                }
            }

            $shortUrl->is_active = !$shortUrl->is_active;
            $shortUrl->save();

            // Invalidate cache
            Cache::forget('short_url:' . $shortUrl->short_code);

            return back()->with('success', 'Link status updated');
        }
}
