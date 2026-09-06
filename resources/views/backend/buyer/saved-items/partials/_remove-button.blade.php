<form method="POST" action="{{ route('buyer.saved-items.toggle') }}" class="inline"
      onsubmit="return confirmSwal(this, 'Remove from Saved Items?', 'Are you sure you want to remove this item from your saved list?', 'warning', 'Yes, Remove')">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="id" value="{{ $item->id }}">
    <button type="submit" title="Remove from saved" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
</form>
