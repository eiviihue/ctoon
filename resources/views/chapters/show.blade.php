@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex flex-column position-relative">
  <nav class="navbar navbar-expand-lg navbar-light bg-light bg-opacity-75 fixed-top border-bottom">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between w-100">
        <div>
          <a href="{{ route('comics.show', $comic) }}" 
             class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Comic Info
          </a>
        </div>

        <div class="d-flex align-items-center gap-2">
          @php
            $prevChapter = $comic->chapters->where('number', '<', $chapter->number)->sortByDesc('number')->first();
            $nextChapter = $comic->chapters->where('number', '>', $chapter->number)->sortBy('number')->first();
          @endphp
          
          @if($prevChapter)
            <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $prevChapter]) }}" 
               class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
              <i class="fas fa-chevron-left"></i>
              Prev Chapter
            </a>
          @endif
          
          <span class="badge bg-primary fs-6">
            Chapter {{ $chapter->number }}
          </span>
          
          @if($nextChapter)
            <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $nextChapter]) }}" 
               class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
              Next Chapter
              <i class="fas fa-chevron-right"></i>
            </a>
          @endif
        </div>
      </div>
    </div>
  </nav>

  <div id="reader" class="container-fluid mt-5 pt-4" data-comic-id="{{ $comic->id }}">
    @php
      $pageUrls = $chapter->pages->map(function($p) { return $p->image_url; })->toArray();
      $total = count($pageUrls);
    @endphp

    @if($total)
      <div class="position-fixed top-50 start-0 end-0 translate-middle-y d-flex justify-content-between px-3" style="z-index: 1000;" aria-hidden="true">
        <button id="prevBtn" class="btn btn-lg btn-dark bg-opacity-50" title="Previous page">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button id="nextBtn" class="btn btn-lg btn-dark bg-opacity-50" title="Next page">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <div class="text-center" data-reader-container>
        <img id="pageImage" data-src="{{ $pageUrls[0] }}" src="{{ $pageUrls[0] }}" alt="Page {{ $chapter->number }}" 
             class="img-fluid mx-auto" style="max-height: 85vh;" />
      </div>

      <nav class="navbar navbar-light bg-light bg-opacity-75 fixed-bottom border-top">
        <div class="container">
          <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-3">
              <button id="fullscreenBtn" class="btn btn-primary" title="Toggle fullscreen">
                <i class="fas fa-expand"></i>
              </button>
              <div class="small text-muted">Page <span id="currentPage">1</span> of <span id="totalPages">{{ $total }}</span></div>
            </div>

            <div class="d-flex align-items-center gap-3">
              <input id="pageInput" type="number" min="1" max="{{ $total }}" value="1" class="form-control form-control-sm" style="width: 80px" />
              <small class="text-muted">Click image, use arrows or swipe to navigate</small>
            </div>
          </div>
        </div>
      </nav>

      <div class="progress fixed-top" style="height: 2px;">
        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
      </div>

      <script>
        (function(){
          const pages = @json($pageUrls);
          let index = 0;
          const img = document.getElementById('pageImage');
          const currentPageEl = document.getElementById('currentPage');
          const totalEl = document.getElementById('totalPages');
          const progressBar = document.getElementById('progressBar');
          const prevBtn = document.getElementById('prevBtn');
          const nextBtn = document.getElementById('nextBtn');
          const fullscreenBtn = document.getElementById('fullscreenBtn');
          const pageInput = document.getElementById('pageInput');

          function updateUI(){
            currentPageEl.textContent = index + 1;
            pageInput.value = index + 1;
            const progress = ((index + 1) / pages.length) * 100;
            progressBar.style.width = progress + '%';
          }

          function preload(i){
            if (i >=0 && i < pages.length){
              const p = new Image(); p.src = pages[i];
            }
          }

          function show(){
            if (!pages[index]) return;
            img.src = pages[index];
            updateUI();
            preload(index + 1);
            preload(index - 1);
          }

          function nextPage(){ if (index < pages.length - 1) { index++; show(); }}
          function prevPage(){ if (index > 0) { index--; show(); }}

          if (img){
            img.addEventListener('click', function(e){
              const rect = img.getBoundingClientRect();
              const x = e.clientX - rect.left;
              if (x > rect.width / 2) nextPage(); else prevPage();
            });

            document.addEventListener('keydown', function(e){
              if (e.key === 'ArrowRight' || e.key === ' ') nextPage();
              else if (e.key === 'ArrowLeft') prevPage();
            });

            // touch
            let touchStartX = 0;
            img.addEventListener('touchstart', function(e){ touchStartX = e.touches[0].clientX; });
            img.addEventListener('touchend', function(e){ const endX = e.changedTouches[0].clientX; const diff = touchStartX - endX; if (Math.abs(diff) > 50) { diff > 0 ? nextPage() : prevPage(); } });

            // nav buttons
            prevBtn && prevBtn.addEventListener('click', prevPage);
            nextBtn && nextBtn.addEventListener('click', nextPage);

            // page input
            pageInput && pageInput.addEventListener('change', function(e){ let v = parseInt(e.target.value) - 1; if (isNaN(v)) v = 0; if (v < 0) v = 0; if (v > pages.length - 1) v = pages.length - 1; index = v; show(); });

            // fullscreen
            fullscreenBtn && fullscreenBtn.addEventListener('click', function(){
              const reader = document.getElementById('reader');
              if (!document.fullscreenElement){ if(reader.requestFullscreen) reader.requestFullscreen(); }
              else { if(document.exitFullscreen) document.exitFullscreen(); }
            });

            // initial
            show();
          }
        })();
      </script>
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

@endsection
