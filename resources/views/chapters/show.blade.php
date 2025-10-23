@extends('layouts.app')

@section('content')
  <div class="flex items-center gap-4 mb-4">
    <a href="{{ route('comics.show', $comic) }}" class="px-3 py-2 border rounded">Comic Info</a>
    @php
      $prevChapter = $comic->chapters->where('number', '<', $chapter->number)->sortByDesc('number')->first();
      $nextChapter = $comic->chapters->where('number', '>', $chapter->number)->sortBy('number')->first();
    @endphp
    @if($prevChapter)
      <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $prevChapter]) }}" class="px-3 py-2 border rounded">Prev Chapter</a>
    @endif
    @if($nextChapter)
      <a href="{{ route('chapters.show', ['comic' => $comic, 'chapter' => $nextChapter]) }}" class="px-3 py-2 border rounded">Next Chapter</a>
    @endif
  </div>

  <div id="reader" class="max-w-3xl mx-auto">
    @php
      $pageUrls = $chapter->pages->map(function($p) { return $p->image_url; })->toArray();
    @endphp
    @if(count($pageUrls))
      <img id="pageImage" src="{{ $pageUrls[0] }}" alt="Page" class="w-full border" />
    @else
      <div class="w-full h-64 bg-gray-100 flex items-center justify-center">No pages</div>
    @endif
  </div>

  <div class="mt-4 text-center text-sm text-gray-600">Click right side of image for next page, left for previous.</div>

  <script>
    (function() {
      const pages = @json($pageUrls);
      let index = 0;
      const img = document.getElementById('pageImage');

      function show() {
        if (!pages[index]) return;
        img.src = pages[index];
      }

      if (img) {
        img.addEventListener('click', function(e) {
          const rect = img.getBoundingClientRect();
          const x = e.clientX - rect.left;
          if (x > rect.width / 2) {
            // next
            if (index < pages.length - 1) index++;
          } else {
            if (index > 0) index--;
          }
          show();
        });
      }

    })();
  </script>
@endsection
