<form method="POST" action="{{ route('admin.students.destroy', $student) }}"
      onsubmit="return confirm('Permanently delete this student account, profile, academic results, saved scholarships and recommendation logs?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline text-sm px-3 py-1.5 text-error">Delete</button>
</form>
