@props(['size' => 'w-10 h-10', 'text' => 'text-base'])
{{-- รูปโปรไฟล์ของผู้ใช้ที่ login ถ้ายังไม่มีรูป (404) แสดงตัวอักษรแรกของชื่อแทน
     ?v= เปลี่ยนทุกครั้งที่อัปโหลดใหม่ เบราว์เซอร์จะไม่ใช้รูปเก่าที่ cache ไว้ --}}
<span {{ $attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-orange-50 text-[#F26522] font-bold border border-slate-200 $size $text"]) }}>
    <span aria-hidden="true">{{ mb_substr(session('user.name') ?? '?', 0, 1) }}</span>
    <img src="/profile/avatar?v={{ session('avatar_v', 0) }}" alt="รูปโปรไฟล์"
         class="absolute inset-0 w-full h-full object-cover"
         onerror="this.remove()">
</span>
