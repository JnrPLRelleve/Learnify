 // Only get buttons that exist
const btnUsername = document.getElementById('btnUsername');
const btnProfilePic = document.getElementById('btnProfilePic');
const content = document.getElementById('settingsContent');
function setActive(btn) {
    [btnUsername, btnProfilePic].forEach(b => b && b.classList.remove('active'));
    btn.classList.add('active');
}
btnUsername.onclick = function() {
    setActive(btnUsername);
    content.innerHTML = `
        <div style="width:100%;max-width:400px;margin:auto;">
            <h2>Change Credentials</h2>
            <div style="display:flex;gap:10px;justify-content:center;margin-bottom:18px;">
                <button id="showChangeUsername" style="padding:8px 18px;">Change Username</button>
                <button id="showChangePassword" style="padding:8px 18px;">Change Password</button>
            </div>
            <div id="credFormArea"></div>
        </div>
    `;
document.getElementById('showChangeUsername').onclick = function() {
    document.getElementById('credFormArea').innerHTML = `
        <form id="changeUsernameForm">
            <label>New Username:<br><input type="text" name="new_username" required style="width:100%;padding:8px;"></label><br><br>
            <label>Current Password:<br><input type="password" name="password" required style="width:100%;padding:8px;"></label><br><br>
            <button type="submit">Change Username</button>
            <div id="usernameMsg" style="margin-top:10px;"></div>
        </form>
    `;
    document.getElementById('changeUsernameForm').onsubmit = function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'change_username');
        fetch('STU_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('usernameMsg').innerText = data.message;
            if(data.success) setTimeout(()=>window.location.reload(), 1200);
        });
    };
};
document.getElementById('showChangePassword').onclick = function() {
    document.getElementById('credFormArea').innerHTML = `
        <form id="changePasswordForm">
            <label>Current Password:<br><input type="password" name="current_password" required style="width:100%;padding:8px;"></label><br><br>
            <label>New Password:<br><input type="password" name="new_password" required style="width:100%;padding:8px;"></label><br><br>
            <label>Confirm New Password:<br><input type="password" name="confirm_password" required style="width:100%;padding:8px;"></label><br><br>
            <button type="submit">Change Password</button>
            <div id="passwordMsg" style="margin-top:10px;"></div>
        </form>
    `;
    document.getElementById('changePasswordForm').onsubmit = function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'change_password');
        fetch('STU_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('passwordMsg').innerText = data.message;
            if(data.success) setTimeout(()=>window.location.reload(), 1200);
        });
    };
};
};
btnProfilePic.onclick = function() {
setActive(btnProfilePic);
content.innerHTML = `<div style="width:100%;">
            <h2>Change Profile Picture</h2>
            <form id="profilePicForm" enctype="multipart/form-data" method="post">
                <input type="file" name="profile_pic" accept="image/*" required><br><br>
                <button type="submit">Upload</button>
            </form>
            <div id="uploadMsg"></div>
        </div>`;
document.getElementById('profilePicForm').onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('STU_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('uploadMsg').innerText = data.message;
        if(data.success) setTimeout(()=>window.location.reload(), 1200);
    });
};
};