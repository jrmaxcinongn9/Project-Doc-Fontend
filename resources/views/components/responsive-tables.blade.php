{{-- ตารางที่ใส่ class stack-on-mobile จะกลายเป็นการ์ดเรียงลงมาบนจอแคบกว่า 768px
     ชื่อคอลัมน์ดึงจากหัวตารางให้อัตโนมัติ ไม่ต้องแก้ทีละตาราง
     เดิมบนมือถือต้องเลื่อนตารางซ้ายขวา ปุ่มจัดการอยู่นอกจอ --}}
<style>
@media (max-width: 767.98px) {
    table.stack-on-mobile { min-width: 0 !important; width: 100%; border-collapse: separate; }
    table.stack-on-mobile thead { display: none; }
    table.stack-on-mobile, table.stack-on-mobile tbody, table.stack-on-mobile tr, table.stack-on-mobile td { display: block; width: 100%; }
    table.stack-on-mobile tbody tr { border: 1px solid #e2e8f0; border-radius: 14px; padding: 6px 4px; margin: 0 0 12px; background: #fff; }
    table.stack-on-mobile tbody td { display: flex; justify-content: space-between; align-items: center; gap: 12px; text-align: right; padding: 8px 12px !important; border: 0 !important; width: auto !important; }
    table.stack-on-mobile tbody td::before { content: attr(data-label); color: #64748b; font-size: 12px; font-weight: 600; text-align: left; flex-shrink: 0; max-width: 45%; }
    table.stack-on-mobile tbody td[data-label=""]::before { content: none; }
    table.stack-on-mobile tbody td[data-label=""] { justify-content: flex-end; }
    /* แถวว่าง เช่น "ยังไม่มีข้อมูล" ที่กินทุกคอลัมน์ ไม่ต้องมีหัวข้อ */
    table.stack-on-mobile tbody td[colspan] { justify-content: center; text-align: center; }
    table.stack-on-mobile tbody td[colspan]::before { content: none; }
}
</style>
<script>
(function () {
    function label(table) {
        var heads = [].map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            [].forEach.call(tr.children, function (td, i) {
                if (!td.hasAttribute('data-label')) td.setAttribute('data-label', heads[i] || '');
            });
        });
    }
    function run() { document.querySelectorAll('table.stack-on-mobile').forEach(label); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else run();
})();
</script>
