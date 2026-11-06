{{-- เลือกรูปโปรไฟล์ เห็นตัวอย่างก่อนบันทึก ใช้ได้ทุกบทบาท --}}
<div class="flex flex-col items-center mb-4">
    <div class="relative">
        <x-avatar size="w-28 h-28" text="text-4xl" id="avatarCurrent" />
        <img id="avatarPreview" alt="ตัวอย่างรูปใหม่" class="hidden absolute inset-0 w-28 h-28 rounded-full object-cover border-4 border-white shadow">
    </div>

    @if(session('avatar_success'))
        <p class="mt-3 text-sm text-emerald-600">{{ session('avatar_success') }}</p>
    @endif
    @error('avatar')
        <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <form method="POST" action="/profile/avatar" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center justify-center gap-2">
        @csrf
        <label class="cursor-pointer px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:border-[#F26522]/50 hover:text-[#F26522]">
            เลือกรูป
            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" id="avatarInput" required>
        </label>
        <button type="submit" id="avatarSave" class="hidden px-4 py-2 rounded-xl bg-[#F26522] hover:bg-[#d8561b] text-white text-sm font-semibold">
            บันทึกรูป
        </button>
    </form>

    <form method="POST" action="/profile/avatar" class="mt-1" onsubmit="return confirm('ลบรูปโปรไฟล์?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-slate-400 hover:text-red-500">ลบรูปโปรไฟล์</button>
    </form>
    <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, WebP หรือ GIF ไม่เกิน 2 MB</p>
</div>

<script>
document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files[0];
    const preview = document.getElementById('avatarPreview');
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('รูปใหญ่เกิน 2 MB');
        this.value = '';
        return;
    }
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
    document.getElementById('avatarSave').classList.remove('hidden');
});
</script>
