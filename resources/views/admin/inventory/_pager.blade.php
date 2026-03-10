@if ($paginator->hasPages())
  <div class="pager">
    @if ($paginator->onFirstPage())
      <span class="btn secondary disabled">Previous</span>
    @else
      <a class="btn secondary" href="{{ $paginator->previousPageUrl() }}">Previous</a>
    @endif

    <span class="pager-meta">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

    @if ($paginator->hasMorePages())
      <a class="btn secondary" href="{{ $paginator->nextPageUrl() }}">Next</a>
    @else
      <span class="btn secondary disabled">Next</span>
    @endif
  </div>
@endif
