@php
  function stepClass($n,$current){ return $n < $current ? 'step done' : ($n==$current ? 'step active' : 'step'); }
@endphp
<div class="progress">
  <div class="{{ stepClass(1,$step) }}"><div></div><small>Date & Time</small></div>
  <div class="{{ stepClass(2,$step) }}"><div></div><small>Guest Details</small></div>
  <div class="{{ stepClass(3,$step) }}"><div></div><small>Menu</small></div>
  <div class="{{ stepClass(4,$step) }}"><div></div><small>Payment</small></div>
  <div class="{{ stepClass(5,$step) }}"><div></div><small>Confirmation</small></div>
</div>
