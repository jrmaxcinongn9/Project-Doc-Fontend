<x-layouts.app title="ช่วยเหลือ | KMUTT">

@php
    $faqs = [
        ['ไม่ได้รับเมลยืนยันตอนสมัคร',
         'ดูในโฟลเดอร์สแปมหรือจดหมายขยะก่อน ถ้ายังไม่เจอ ให้กลับไปหน้าเข้าสู่ระบบแล้วกด Login ด้วยอีเมลและรหัสผ่านที่สมัครไว้ ระบบจะส่งเมลยืนยันให้อีกครั้ง ลิงก์ในเมลทุกฉบับใช้ได้ 24 ชั่วโมง'],
        ['ยืนยันอีเมลแล้วแต่ยังเข้าระบบไม่ได้',
         'บัญชีนักศึกษาต้องรอผู้ดูแลระบบอนุมัติก่อน เมื่ออนุมัติแล้วจะได้รับอีเมลพร้อมปุ่มเข้าสู่ระบบ กดปุ่มนั้นแล้วเข้าระบบได้ทันที'],
        ['ลืมรหัสผ่าน',
         'ที่หน้าเข้าสู่ระบบ กด Forgot Password แล้วกรอกอีเมล ระบบจะส่งรหัส OTP ไปทางอีเมลเพื่อตั้งรหัสผ่านใหม่ รหัสมีอายุ 10 นาที'],
        ['สมัครแล้วขึ้นว่าอีเมลนี้ถูกใช้แล้ว',
         'แปลว่ามีบัญชีของอีเมลนี้อยู่แล้ว ไม่ต้องสมัครใหม่ ให้เข้าสู่ระบบด้วยอีเมลนั้น ถ้าจำรหัสผ่านไม่ได้ให้ใช้ Forgot Password'],
        ['ต้องใช้อีเมลแบบไหน',
         'ใช้อีเมลของมหาวิทยาลัยที่ลงท้ายด้วย @mail.kmutt.ac.th เท่านั้น'],
        ['เข้าระบบแล้วไม่เห็นห้องเรียน',
         'ระบบเพิ่มนักศึกษาเข้าห้องตามสาขาวิชาโดยอัตโนมัติ ถ้าไม่เห็นห้อง อาจเป็นเพราะอาจารย์ยังไม่ได้สร้างห้องของสาขาคุณ หรือสาขาวิชาในบัญชีไม่ถูกต้อง แจ้งเราได้ที่แบบฟอร์มด้านล่าง'],
        ['ส่งไฟล์ได้ชนิดไหน และขนาดเท่าไร',
         'ส่งได้ไฟล์ละไม่เกิน 5 MB ส่งได้หลายไฟล์ต่องาน ถ้าส่งเป็น PDF หรือรูปภาพ อาจารย์จะเปิดดูในเว็บได้ทันทีโดยไม่ต้องดาวน์โหลด'],
        ['ส่งงานผิดไฟล์ จะแก้อย่างไร',
         'เข้าห้องเรียน แล้วกดแก้ไขงานที่งานชิ้นนั้น ลบไฟล์ที่ส่งผิดแล้วอัปโหลดไฟล์ใหม่ได้'],
        ['ใช้งานอยู่แล้วถูกพากลับไปหน้าเข้าสู่ระบบ',
         'เพื่อความปลอดภัย ระบบให้เข้าใช้งานต่อเนื่องได้ครั้งละ 1 ชั่วโมง หลังจากนั้นต้องเข้าสู่ระบบใหม่'],
    ];
    $u = session('user');
@endphp

<div class="min-h-screen bg-slate-50 text-slate-800">

    <header class="bg-white border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-semibold text-slate-700">
                <span class="w-8 h-8 rounded-lg bg-[#F58220] text-white flex items-center justify-center text-sm">D</span>
                Document Internship
            </a>
            <a href="{{ $u ? url()->previous() : '/login/student' }}" class="text-sm text-slate-500 hover:text-[#F58220]">
                {{ $u ? '← กลับ' : 'ไปหน้าเข้าสู่ระบบ' }}
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-slate-800">ช่วยเหลือ</h1>
        <p class="text-slate-500 mt-1">ลองหาคำตอบจากคำถามที่พบบ่อยก่อน ถ้าไม่เจอ แจ้งปัญหาถึงผู้ดูแลระบบได้ที่ด้านล่าง</p>

        <section class="mt-6 bg-white border border-slate-200 rounded-2xl divide-y divide-slate-100">
            @foreach($faqs as [$q, $a])
                <details class="group px-5 py-4">
                    <summary class="flex justify-between items-center cursor-pointer list-none font-medium text-slate-700">
                        {{ $q }}
                        <span class="text-slate-400 group-open:rotate-45 transition-transform text-xl leading-none">+</span>
                    </summary>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $a }}</p>
                </details>
            @endforeach
        </section>

        <section id="form" class="mt-8 bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-slate-800">แจ้งปัญหาถึงผู้ดูแลระบบ</h2>
            <p class="text-sm text-slate-500 mt-1">ผู้ดูแลระบบจะตอบกลับทางอีเมลที่คุณกรอก</p>

            @if(session('support_sent'))
                <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('support_sent') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/support" class="mt-4 space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">ชื่อ</label>
                        <input name="name" required maxlength="100" value="{{ old('name', $u['name'] ?? '') }}"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#F58220]/30 focus:border-[#F58220]">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">อีเมลที่ใช้ติดต่อกลับ</label>
                        <input name="email" type="email" required value="{{ old('email', $u['email'] ?? '') }}" placeholder="ชื่อ.นามสกุล@mail.kmutt.ac.th"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#F58220]/30 focus:border-[#F58220]">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">หัวข้อ</label>
                    <select name="topic" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-[#F58220]/30 focus:border-[#F58220]">
                        @foreach($topics as $value => $label)
                            <option value="{{ $value }}" @selected(old('topic') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">รายละเอียด</label>
                    <textarea name="message" required minlength="10" maxlength="3000" rows="5"
                              placeholder="เล่าว่าทำอะไรอยู่ แล้วเจออะไร เช่น ข้อความ error ที่ขึ้น"
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#F58220]/30 focus:border-[#F58220]">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#F58220] hover:bg-[#d96f16] text-white text-sm font-semibold">ส่งเรื่อง</button>
            </form>
        </section>
    </main>
</div>

</x-layouts.app>
