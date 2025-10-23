@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex flex-column position-relative">
  <nav class="navbar navbar-expand-lg navbar-light bg-light bg-opacity-75 relative-top border-bottom">
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

          {{-- Toggle panel button (shows comments & rating) --}}
          <button id="togglePanelBtn" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="fas fa-comments"></i>
            <span class="d-none d-lg-inline">Comments</span>
          </button>
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
      <style>
        /* Reader layout + side panel */
        .reader-layout{ display:block; transition: all .25s ease; }
        .reader-main{ transition: margin-right .25s ease; }
        .reader-side{ position: fixed; top: 80px; right: 0; width: 360px; bottom: 70px; background: var(--bs-body-bg); border-left: 1px solid rgba(0,0,0,.08); box-shadow: -6px 0 18px rgba(0,0,0,.05); overflow:auto; transform: translateX(100%); transition: transform .25s ease; z-index:1100; padding:16px; }
        .reader-layout.open .reader-side{ transform: translateX(0); }
        .reader-layout.open .reader-main{ margin-right: 360px; }
        .reader-side .star-inputs .fa-star{ cursor:pointer; color:#ddd; }
        .reader-side .star-inputs .fa-star.selected{ color: #ffc107; }
        @media (max-width: 992px){ .reader-side{ width: 100%; right:0; left:0; top:64px; bottom:0; } .reader-layout.open .reader-main{ margin-right:0; } }
      </style>

      <div class="reader-layout" id="readerLayout">
        <div class="reader-main">
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
        </div>

        <aside class="reader-side" id="readerSide" aria-hidden="true">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Comments & Rating</h5>
            <button id="closePanel" class="btn btn-sm btn-outline-secondary">Close</button>
          </div>

          <div class="mb-3">
                <div class="d-flex align-items-center gap-2">
              <div>
                <strong>Average</strong>
                <div class="h4 mb-0" id="averageRating">{{ number_format($comic->averageRating(), 1) }}</div>
                <small class="text-muted" id="ratingCount">{{ $comic->ratings()->count() }} ratings</small>
              </div>
              <div class="ms-auto">
                @auth
                  <button id="openRate" class="btn btn-sm btn-primary">Give Rating</button>
                @else
                  <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Login to rate</a>
                @endauth
              </div>
            </div>
          </div>

          <div id="rateFormWrap" class="mb-4" style="display:none">
            @auth
            <form id="rateForm" action="{{ route('ratings.store', $comic) }}" method="POST">
              @csrf
              <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
              <div class="star-inputs mb-2">
                @for($i=1;$i<=5;$i++)
                  <i class="fa fa-star fa-lg" data-value="{{ $i }}"></i>
                @endfor
              </div>
              <input type="hidden" name="rating" id="ratingValue" value="0">
              <div class="d-grid">
                <button class="btn btn-success">Submit Rating</button>
              </div>
            </form>
            @endauth
          </div>

          <hr>

          <div>
            <h6>Comments ({{ $chapter->comments()->count() }})</h6>
            <div id="commentsList" class="mb-3">
              @foreach($chapter->comments()->with('user')->latest()->get() as $c)
                <div class="mb-2">
                  <div class="small text-muted">{{ $c->user->name }} · {{ $c->created_at->diffForHumans() }}</div>
                  <div>{{ $c->body }}</div>
                </div>
              @endforeach
            </div>

            @auth
            <form id="commentForm" action="{{ route('comments.store', $comic) }}" method="POST">
              @csrf
              <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
              <div class="mb-2">
                <textarea name="body" rows="3" class="form-control" placeholder="Write a comment..."></textarea>
              </div>
              <div class="d-grid">
                <button class="btn btn-primary">Post Comment</button>
              </div>
            </form>
            @else
              <div class="text-center">
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login to comment</a>
              </div>
            @endauth
          </div>
        </aside>
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

          // Panel toggle and AJAX handlers
          const toggleBtn = document.getElementById('togglePanelBtn');
          const readerLayoutEl = document.getElementById('readerLayout');
          const readerSideEl = document.getElementById('readerSide');
          const closePanel = document.getElementById('closePanel');
          const openRate = document.getElementById('openRate');
          const rateFormWrap = document.getElementById('rateFormWrap');
          const rateForm = document.getElementById('rateForm');
          const commentForm = document.getElementById('commentForm');
          const commentsList = document.getElementById('commentsList');
          const avgEl = document.getElementById('averageRating');
          const countEl = document.getElementById('ratingCount');
          const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : null;

          function openPanel(){ if(readerLayoutEl){ readerLayoutEl.classList.add('open'); readerSideEl && readerSideEl.setAttribute('aria-hidden','false'); }}
          function closePanelFn(){ if(readerLayoutEl){ readerLayoutEl.classList.remove('open'); readerSideEl && readerSideEl.setAttribute('aria-hidden','true'); }}

          toggleBtn && toggleBtn.addEventListener('click', function(){
            if(readerLayoutEl && readerLayoutEl.classList.contains('open')) closePanelFn(); else openPanel();
          });
          closePanel && closePanel.addEventListener('click', closePanelFn);

          // open rate form
          openRate && openRate.addEventListener('click', function(){ if(rateFormWrap) rateFormWrap.style.display = 'block'; openRate.disabled = true; });

          // star selection
          if (rateForm) {
            const stars = rateForm.querySelectorAll('.fa-star');
            const ratingInput = rateForm.querySelector('#ratingValue');
            stars.forEach(s => s.addEventListener('click', function(){
              const val = parseInt(this.dataset.value || 0, 10);
              ratingInput && (ratingInput.value = val);
              stars.forEach(x => x.classList.toggle('selected', parseInt(x.dataset.value,10) <= val));
            }));

            rateForm.addEventListener('submit', async function(e){
              e.preventDefault();
              const formData = new FormData(rateForm);
              try{
                const res = await fetch(rateForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: formData });
                if (!res.ok) throw new Error('Network');
                const json = await res.json();
                if (avgEl && json.average !== undefined) avgEl.textContent = json.average;
                if (countEl && json.totalRatings !== undefined) countEl.textContent = json.totalRatings + ' ratings';
                // keep panel open, disable form
                rateFormWrap.style.display = 'none';
                openRate.disabled = false;
                alert('Rating saved');
              }catch(err){ console.error(err); alert('Failed to save rating'); }
            });
          }

          // comment submit
          if (commentForm) {
            commentForm.addEventListener('submit', async function(e){
              e.preventDefault();
              const fd = new FormData(commentForm);
              try{
                const res = await fetch(commentForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: fd });
                if (!res.ok) throw new Error('Network');
                const json = await res.json();
                const tpl = document.createElement('div'); tpl.className = 'mb-2'; tpl.innerHTML = `<div class="small text-muted">${json.user_name} · just now</div><div>${json.body}</div>`;
                commentsList && commentsList.prepend(tpl);
                commentForm.querySelector('textarea') && (commentForm.querySelector('textarea').value = '');
              }catch(err){ console.error(err); alert('Failed to post comment'); }
            });
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
