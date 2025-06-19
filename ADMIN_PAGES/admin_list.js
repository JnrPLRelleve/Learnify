function openEditAdminModal(userId, fullname, pic) {
    const modalHtml = `
    <div class="modal-bg" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:1000;display:flex;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:32px 28px;border-radius:10px;min-width:340px;max-width:90vw;box-shadow:0 2px 16px rgba(0,0,0,0.18);position:relative;">
        <button onclick="this.closest('.modal-bg').remove()" style="position:absolute;top:10px;right:18px;font-size:1.5em;background:none;border:none;cursor:pointer;">&times;</button>
        <h2>Edit Admin: <span style='font-size:0.8em;'>${fullname}</span></h2>
        <div style="margin-bottom:18px;text-align:center;">
          <img src="${pic}" alt="profile" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
        </div>
        <form id="adminPicForm" enctype="multipart/form-data" method="post">
          <label>Change Profile Picture:<br><input type="file" name="profile_pic" accept="image/*"></label><br><br>
          <input type="hidden" name="user_id" value="${userId}">
          <button type="submit">Upload</button>
        </form>
        <div id="adminPicMsg"></div>
        <hr style="margin:18px 0;">
        <form id="adminPassForm" method="post">
          <label>New Password:<br><input type="password" name="new_password" required></label><br><br>
          <input type="hidden" name="user_id" value="${userId}">
          <button type="submit">Change Password</button>
        </form>
        <div id="adminPassMsg"></div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    document.getElementById('adminPicForm').onsubmit = function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      fetch('edit_admin.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          document.getElementById('adminPicMsg').innerText = data.message;
          if(data.success) setTimeout(()=>window.location.reload(), 1200);
        });
    };
    document.getElementById('adminPassForm').onsubmit = function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      fetch('edit_admin.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          document.getElementById('adminPassMsg').innerText = data.message;
          if(data.success) setTimeout(()=>window.location.reload(), 1200);
        });
    };
}

function deleteAdmin(userId, btn) {
    if (!confirm('Are you sure you want to delete this admin?')) return;
    fetch('delete_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + encodeURIComponent(userId)
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === 'success') {
            // Remove the admin item from the DOM
            btn.closest('.admin-item').remove();
        } else {
            alert('Failed to delete admin.');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var searchBtn = document.getElementById('adminSearchBtn');
    var searchInput = document.getElementById('adminSearchInput');
    if (searchBtn && searchInput) {
        searchBtn.onclick = function() {
            const val = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.admin-item').forEach(function(item) {
                const fullname = item.getAttribute('data-fullname');
                const userid = item.getAttribute('data-userid');
                if (fullname.includes(val) || userid.includes(val)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        };
        searchInput.addEventListener('input', function() {
            searchBtn.click();
        });
    }
});
