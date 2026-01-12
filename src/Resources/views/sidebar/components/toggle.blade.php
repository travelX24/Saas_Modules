@php
    $isRtl = in_array(app()->getLocale(), ['ar','fa','ur']) || (config('app.rtl') === true);
@endphp
<div class="toggle-btn" id="toggleBtn">
    @if($isRtl)
        {{-- RTL: arrow points right (→) when expanded, rotates to left (←) when collapsed --}}
        <i class="fas fa-chevron-right"></i>
    @else
        {{-- LTR: arrow points left (←) when expanded, rotates to right (→) when collapsed --}}
        <i class="fas fa-chevron-left"></i>
    @endif
</div>
