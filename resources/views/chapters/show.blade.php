@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
  <div class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-4">
          <a href="{{ route('comics.show', $comic) }}" 
             class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Comic Info
          </a>
        </div>

        <div class="flex items-center gap-2">
          @php
            $prevChapter = $comic->chapters->where('number', '<', $chapter->number)->sortByDesc('number')->first();
            $nextChapter = $comic->chapters->where('number', '>', $chapter->number)->sortBy('number')->first();
          @endphp
          
          @if($prevChapter)
            <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $prevChapter]) }}" 
               class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              Prev Chapter
            </a>
          @endif
          
          <span class="px-4 py-2 rounded-lg bg-primary/10 text-primary font-medium">
            Chapter {{ $chapter->number }}
          </span>
          
          @if($nextChapter)
            <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $nextChapter]) }}" 
               class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
              Next Chapter
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div id="reader" class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-8">
    @php
      $pageUrls = $chapter->pages->map(function($p) { return $p->image_url; })->toArray();
    @endphp
    @if(count($pageUrls))
      <img id="pageImage" src="{{ $pageUrls[0] }}" alt="Page {{ $chapter->number }}" 
           class="w-full max-h-[calc(100vh-8rem)] object-contain mx-auto rounded-lg shadow-lg" />
      
      <div class="fixed bottom-0 inset-x-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Page <span id="currentPage">1</span> of {{ count($pageUrls) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Click the image or use arrow keys to navigate
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="flex flex-col items-center justify-center h-[calc(100vh-8rem)]">
        <div class="p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100">No pages available</h3>
          <p class="mt-2 text-gray-500 dark:text-gray-400">This chapter doesn't have any pages yet.</p>
        </div>
      </div>
    @endif
  </div>
</div>

<script>
    (function() {
      const pages = @json($pageUrls);
      let index = 0;
      const img = document.getElementById('pageImage');
      const currentPageEl = document.getElementById('currentPage');

      function show() {
        if (!pages[index]) return;
        img.src = pages[index];
        currentPageEl.textContent = index + 1;
        
        // Preload next and previous images for smoother navigation
        if (index < pages.length - 1) {
          const nextImg = new Image();
          nextImg.src = pages[index + 1];
        }
        if (index > 0) {
          const prevImg = new Image();
          prevImg.src = pages[index - 1];
        }
      }

      function nextPage() {
        if (index < pages.length - 1) {
          index++;
          show();
        }
      }

      function prevPage() {
        if (index > 0) {
          index--;
          show();
        }
      }

      if (img) {
        // Click navigation
        img.addEventListener('click', function(e) {
          const rect = img.getBoundingClientRect();
          const x = e.clientX - rect.left;
          if (x > rect.width / 2) {
            nextPage();
          } else {
            prevPage();
          }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
          if (e.key === 'ArrowRight' || e.key === ' ') {
            nextPage();
          } else if (e.key === 'ArrowLeft') {
            prevPage();
          }
        });

        // Touch navigation
        let touchStartX = 0;
        img.addEventListener('touchstart', function(e) {
          touchStartX = e.touches[0].clientX;
        });

        img.addEventListener('touchend', function(e) {
          const touchEndX = e.changedTouches[0].clientX;
          const diff = touchStartX - touchEndX;

          if (Math.abs(diff) > 50) { // Minimum swipe distance
            if (diff > 0) {
              nextPage(); // Swipe left = next page
            } else {
              prevPage(); // Swipe right = previous page
            }
          }
        });

        // Prevent default touch behavior (zooming, scrolling)
        img.addEventListener('touchmove', function(e) {
          e.preventDefault();
        }, { passive: false });

        // Load first image
        show();
      }
    })();
  </script>
@endsection
