<div class="progress">
  <div class="step {{ 1 < $step ? 'done' : (1 === (int) $step ? 'active' : '') }}">
    <div class="dot">{{ 1 }}</div>
    <small class="label">Date & Time</small>
  </div>
  <div class="step {{ 2 < $step ? 'done' : (2 === (int) $step ? 'active' : '') }}">
    <div class="dot">{{ 2 }}</div>
    <small class="label">Guest Details</small>
  </div>
  <div class="step {{ 3 < $step ? 'done' : (3 === (int) $step ? 'active' : '') }}">
    <div class="dot">{{ 3 }}</div>
    <small class="label">Menu</small>
  </div>
  <div class="step {{ 4 < $step ? 'done' : (4 === (int) $step ? 'active' : '') }}">
    <div class="dot">{{ 4 }}</div>
    <small class="label">Payment</small>
  </div>
  <div class="step {{ 5 < $step ? 'done' : (5 === (int) $step ? 'active' : '') }}">
    <div class="dot">{{ 5 }}</div>
    <small class="label">Confirmation</small>
  </div>
</div>
