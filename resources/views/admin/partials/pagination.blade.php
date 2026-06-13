@if ($paginator->hasPages())
  <div class="admin-pagination">
    <div class="admin-pagination-meta">
      Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    <div class="admin-pagination-actions">
      @if ($paginator->onFirstPage())
        <span class="btn secondary disabled">Previous</span>
      @else
        <a class="btn secondary" href="{{ $paginator->previousPageUrl() }}">Previous</a>
      @endif

      <span class="admin-pagination-page">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

      @if ($paginator->hasMorePages())
        <a class="btn secondary" href="{{ $paginator->nextPageUrl() }}">Next</a>
      @else
        <span class="btn secondary disabled">Next</span>
      @endif
    </div>
  </div>
@elseif ($paginator->total() > 0)
  <div class="admin-pagination">
    <div class="admin-pagination-meta">
      Showing {{ $paginator->total() }} {{ \Illuminate\Support\Str::plural('record', $paginator->total()) }}
    </div>
  </div>
@endif
