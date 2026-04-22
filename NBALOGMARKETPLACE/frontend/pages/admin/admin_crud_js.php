<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function closeModal(){document.getElementById('itemModal').classList.remove('active');}
  function showToast(msg,type='success'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='success'?'circle-check':'circle-exclamation'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',4000);}
  function showFormAlert(msg,type='error'){const el=document.getElementById('formAlert');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-circle-exclamation"></i> ${msg}`;el.style.display='flex';}

  function openAdd() {
    document.getElementById('itemId').value = '';
    document.getElementById('modalTitle').textContent = document.querySelector('.page-title').textContent.trim().replace(/[^\w\s&]/g,'').trim();
    document.getElementById('formAlert').style.display = 'none';
    // Clear all fields
    ['fTitle','fDesc','fLink','fPrice','fLink2','fPlatform'].forEach(id => {
      const el = document.getElementById(id); if(el) el.value = '';
    });
    const fActive = document.getElementById('fActive'); if(fActive) fActive.value = '1';
    const fSample = document.getElementById('fSample'); if(fSample) fSample.value = '';
    const preview = document.getElementById('samplePreview'); if(preview) preview.style.display='none';
    document.getElementById('itemModal').classList.add('active');
  }

  function openEdit(item) {
    document.getElementById('itemId').value = item.id;
    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('formAlert').style.display = 'none';
    // Fill common fields
    const fields = {fTitle:'title',fDesc:'description',fLink:'link',fPrice:'price',fActive:'is_active',fLink2:'credentials',fPlatform:'platform',fSample:'sample_image'};
    for (const [elId, key] of Object.entries(fields)) {
      const el = document.getElementById(elId);
      if (el && item[key] !== undefined) el.value = item[key];
    }
    // Show sample image preview if present
    const preview = document.getElementById('samplePreview');
    if (preview && item.sample_image) {
      preview.src = item.sample_image; preview.style.display = 'block';
    }
    document.getElementById('itemModal').classList.add('active');
  }

  async function saveItem(type) {
    const id     = document.getElementById('itemId').value;
    const title  = document.getElementById('fTitle')?.value.trim();
    const desc   = document.getElementById('fDesc')?.value.trim();
    const link   = document.getElementById('fLink')?.value.trim();
    const link2  = document.getElementById('fLink2')?.value.trim();
    const price  = document.getElementById('fPrice')?.value;
    const active = document.getElementById('fActive')?.value;
    const sample = document.getElementById('fSample')?.value.trim();
    const platform = document.getElementById('fPlatform')?.value.trim();

    if (!title) { showFormAlert('Title is required.'); return; }

    const btn = document.getElementById('saveBtn');
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Saving...';

    const payload = {id, type, title, description:desc, link, link2, price, is_active:active, sample_image:sample, platform, credentials:link2};

    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/save_item.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
      const data = await res.json();
      if (data.success) {
        closeModal();
        showToast(data.message || 'Saved successfully!');
        setTimeout(() => location.reload(), 900);
      } else {
        showFormAlert(data.message || 'Save failed.');
        btn.disabled = false; btn.innerHTML = orig;
      }
    } catch(e) {
      showFormAlert('Connection error.'); btn.disabled = false; btn.innerHTML = orig;
    }
  }

  async function doDelete(id, type) {
    if (!confirm('Delete this item? This cannot be undone.')) return;
    const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/delete_item.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,type})});
    const data = await res.json();
    if (data.success) {
      const row = document.getElementById('row'+id); if(row) row.remove();
      showToast('Deleted successfully.');
    } else {
      showToast(data.message || 'Delete failed.','error');
    }
  }

  // Preview sample image URL
  function previewSample() {
    const url = document.getElementById('fSample')?.value.trim();
    const preview = document.getElementById('samplePreview');
    if (preview) { preview.src = url; preview.style.display = url ? 'block' : 'none'; }
  }

  document.getElementById('itemModal')?.addEventListener('click',function(e){if(e.target===this)closeModal();});
</script>