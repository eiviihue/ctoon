@props(['comic', 'size' => 'sm'])

<div class="mini-rating">
    <div class="mini-stars">
        @for ($i = 1; $i <= 5; $i++)
            <i class="fa-solid fa-star mini-star"></i>
        @endfor
    </div>
    <div class="mini-rating-stats">
        <span class="mini-average">{{ number_format($comic->average_rating ?? 0, 1) }}</span>
        <span class="mini-count">({{ $comic->ratings_count ?? 0 }})</span>
    </div>
</div>

@push('scripts')
<script type="module">
    import { initRating } from '{{ asset("js/rating.js") }}';
    initRating({{ $comic->id }});
</script>
@endpush