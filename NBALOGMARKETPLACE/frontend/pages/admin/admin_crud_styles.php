<style>
  .add-btn{padding:9px 18px;background:linear-gradient(135deg,#1a8cff,#0057b8);color:white;border:none;border-radius:9px;font-family:'Outfit',sans-serif;font-weight:700;font-size:.88rem;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all .2s}
  .add-btn:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(26,140,255,.35)}
  .crud-table{width:100%;border-collapse:collapse}
  .crud-table th{background:#f5fbff;padding:11px 14px;text-align:left;font-size:.75rem;font-weight:700;color:#6b8ba4;text-transform:uppercase;letter-spacing:.5px;border-bottom:1.5px solid #e8f4ff;white-space:nowrap}
  .crud-table td{padding:13px 14px;border-bottom:1px solid #f0f7ff;font-size:.88rem;vertical-align:middle}
  .crud-table tr:last-child td{border-bottom:none}
  .crud-table tr:hover td{background:#fafcff}
  .count-badge{background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700;margin-left:6px}
  .status-dot{padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700}
  .status-dot.active{background:#e6faf5;color:#0a8c6a}
  .status-dot.inactive{background:#f0f0f0;color:#6b8ba4}
  .act-btn{padding:6px 12px;border-radius:7px;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;display:inline-flex;align-items:center;gap:5px}
  .act-btn.edit{background:#e8f4ff;color:#1a8cff;border:1.5px solid #b3d9ff}
  .act-btn.edit:hover{background:#d6ecff}
  .act-btn.del{background:#fff0f0;color:#c0392b;border:1.5px solid #f5c6c6}
  .act-btn.del:hover{background:#ffe0e0}
  .truncate{max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .empty-td{text-align:center;padding:40px;color:#6b8ba4}
  /* Modal */
  .modal-overlay{position:fixed;inset:0;background:rgba(13,27,42,.6);backdrop-filter:blur(6px);z-index:9000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all .28s}
  .modal-overlay.active{opacity:1;visibility:visible}
  .modal-box{background:white;border-radius:22px;padding:36px;width:90%;max-width:500px;transform:scale(.93) translateY(20px);transition:all .28s;max-height:90vh;overflow-y:auto}
  .modal-overlay.active .modal-box{transform:scale(1) translateY(0)}
  .modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
  .modal-title{font-family:'Outfit',sans-serif;font-size:1.15rem;font-weight:800;color:#0d1b2a}
  .modal-close{width:34px;height:34px;border-radius:50%;border:none;cursor:pointer;background:#f0f7ff;color:#6b8ba4;font-size:1rem;display:flex;align-items:center;justify-content:center}
  .modal-close:hover{background:#e8f4ff;color:#1a8cff}
  .save-btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a8cff,#0057b8);color:white;border:none;border-radius:10px;font-family:'Outfit',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:8px}
  .save-btn:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(26,140,255,.35)}
  .sample-preview{width:100%;height:140px;object-fit:cover;border-radius:10px;margin-bottom:10px;border:1.5px solid #d6ecff}
</style>