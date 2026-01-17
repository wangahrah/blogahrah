<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Photos - Admin</title>
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
  <link rel="apple-touch-icon" href="/img/favicon.ico">
  <link rel="stylesheet" href="/main.css">
  <style>
    * { box-sizing: border-box; }

    .write-content {
      min-height: 300px;
      padding: 20px;
      font-family: monospace;
      color: #00FF00;
      text-align: left;
      background-color: black;
    }

    h1 { color: #00ff00; margin-bottom: 20px; font-family: monospace; }

    /* Login form */
    #login-form {
      max-width: 300px;
      margin: 50px auto;
      padding: 30px;
      background: black;
      border: 1px solid #333;
    }
    #login-form input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #333;
      background: black;
      color: #00FF00;
      font-size: 16px;
      font-family: monospace;
    }
    #login-form input:focus {
      outline: 1px solid #00FF00;
    }
    #login-form button {
      width: 100%;
      padding: 12px;
      background: #00ff00;
      color: #000;
      border: none;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
      font-family: monospace;
    }
    #login-form button:hover { background: #00cc00; }
    .error { color: #ff4444; margin-bottom: 15px; font-family: monospace; }

    /* Editor */
    #editor-container { display: none; }
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }
    .form-row input, .form-row textarea {
      flex: 1;
      padding: 12px;
      border: 1px solid #333;
      background: black;
      color: #00FF00;
      font-size: 16px;
      font-family: monospace;
    }
    .form-row input:focus, .form-row textarea:focus {
      outline: 1px solid #00FF00;
    }
    .form-row input[type="date"] { max-width: 200px; }
    .form-row input[type="number"] { max-width: 100px; }
    .form-row input[type="date"]::-webkit-calendar-picker-indicator {
      filter: invert(1);
    }

    .form-row textarea {
      min-height: 150px;
      resize: vertical;
      line-height: 1.6;
    }

    /* Image upload area */
    .upload-area {
      border: 2px dashed #333;
      padding: 40px;
      text-align: center;
      margin-bottom: 15px;
      cursor: pointer;
      transition: border-color 0.2s;
    }
    .upload-area:hover, .upload-area.dragover {
      border-color: #00ff00;
    }
    .upload-area p {
      margin: 0;
      color: #666;
    }
    .upload-area.has-image {
      padding: 10px;
    }
    .upload-area img {
      max-width: 100%;
      max-height: 400px;
      display: block;
      margin: 0 auto;
      border: 1px solid #333;
    }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    .btn {
      padding: 12px 24px;
      border: 1px solid #333;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
      font-family: monospace;
    }
    .btn-primary { background: #00ff00; color: #000; border-color: #00ff00; }
    .btn-primary:hover { background: #00cc00; }
    .btn-secondary { background: black; color: #00FF00; }
    .btn-secondary:hover { background: #111; }
    .btn-danger { background: black; color: #ff4444; border-color: #ff4444; }
    .btn-danger:hover { background: #1a0000; }

    .status { margin-top: 15px; padding: 10px; border: 1px solid #333; font-family: monospace; }
    .status.success { background: black; color: #00ff00; border-color: #00ff00; }
    .status.error { background: black; color: #ff4444; border-color: #ff4444; }

    /* Mobile responsive styles */
    @media screen and (max-width: 768px) {
      .write-content {
        padding: 15px;
      }

      h1 {
        font-size: 1.5em;
      }

      .form-row {
        flex-direction: column;
        gap: 10px;
      }

      .form-row input[type="date"],
      .form-row input[type="number"] {
        max-width: 100%;
      }

      .actions {
        flex-wrap: wrap;
      }

      .btn {
        flex: 1;
        min-width: 120px;
        padding: 10px 15px;
      }

      .upload-area {
        padding: 30px 15px;
      }
    }

    @media screen and (max-width: 480px) {
      .write-content {
        padding: 12px;
      }

      h1 {
        font-size: 1.3em;
      }

      #login-form {
        margin: 30px auto;
        padding: 20px;
      }

      .form-row input, .form-row textarea {
        padding: 10px;
        font-size: 14px;
      }

      .btn {
        padding: 10px 12px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div id="head">
    <a href="/"><img id="logo" alt="logo" src="/img/logo.png"></a>
    <ul id="navlist">
      <li><a href="/blogahrah/">blogahrah</a></li>
      <li><a href="/photos/">photos</a></li>
      <li><a href="https://www.linkedin.com/in/michaelanfang/" target="_blank">linkedin</a></li>
      <li><a href="https://anarchyincubator.com/" target="_blank">anarchy incubator</a></li>
      <li><a href="https://divinemetaphor.com/" target="_blank">divine metaphor</a></li>
    </ul>
  </div>

  <div class="container">
    <div class="write-content">
      <!-- Login Form -->
      <div id="login-form">
        <h1>speak friend and enter</h1>
        <div id="login-error" class="error" style="display:none;"></div>
        <input type="password" id="password" placeholder="Password" autofocus>
        <button onclick="login()">Enter</button>
      </div>

      <!-- Editor (hidden until logged in) -->
      <div id="editor-container">
        <h1 id="editor-heading">New Photo</h1>

        <!-- Image upload -->
        <div id="upload-area" class="upload-area" onclick="document.getElementById('file-input').click()">
          <p>Click or drag image here to upload</p>
        </div>
        <input type="file" id="file-input" accept="image/*" style="display:none;" onchange="handleFileSelect(event)">

        <div class="form-row">
          <input type="text" id="title" placeholder="Photo title (optional)">
        </div>

        <div class="form-row">
          <textarea id="description" placeholder="Description / blurb (optional)"></textarea>
        </div>

        <div class="form-row">
          <input type="date" id="date">
          <input type="number" id="order" placeholder="Order" min="0" value="0">
        </div>

        <div class="form-row" style="justify-content: flex-start;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: monospace;">
            <input type="checkbox" id="private" style="width: auto; accent-color: #00ff00;">
            <span>Make private (only visible when logged in)</span>
          </label>
        </div>

        <div class="actions">
          <button class="btn btn-primary" id="save-btn" onclick="savePhoto()">Publish</button>
          <button class="btn btn-secondary" onclick="newPhoto()">New Photo</button>
          <button class="btn btn-danger" id="delete-btn" onclick="deletePhoto()" style="display:none;">Delete</button>
          <button class="btn btn-secondary" onclick="logout()">Logout</button>
        </div>

        <div id="status" class="status" style="display:none;"></div>
      </div>
    </div>
  </div>

  <footer class="footer">
    <ul class="footlist">
      <li>copyright 2025</li>
      <li><a href="/blogahrah/">blogahrah</a></li>
      <li><a href="/photos/">photos</a></li>
      <li><a href="https://www.linkedin.com/in/michaelanfang/" target="_blank">linkedin</a></li>
      <li><a href="https://anarchyincubator.com/" target="_blank">anarchy incubator</a></li>
      <li><a href="https://divinemetaphor.com/" target="_blank">divine metaphor</a></li>
      <li><a href="/legal.html">legal</a></li>
    </ul>
  </footer>

  <script>
    let csrfToken = null;
    let editingId = null;
    let currentImage = null;

    // Check auth on load
    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('date').value = new Date().toISOString().split('T')[0];

      const res = await fetch('/api/auth.php');
      const data = await res.json();

      if (data.authenticated) {
        csrfToken = data.csrf_token;
        showEditor();
        checkEditParam();
      }

      // Enter key to login
      document.getElementById('password').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') login();
      });

      // Drag and drop
      const uploadArea = document.getElementById('upload-area');
      uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
      });
      uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
      });
      uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
          uploadFile(file);
        }
      });
    });

    function checkEditParam() {
      const params = new URLSearchParams(window.location.search);
      const id = params.get('edit');
      if (id) {
        loadPhoto(id);
      }
    }

    async function loadPhoto(id) {
      try {
        const res = await fetch('/api/photos.php');
        const photos = await res.json();
        const photo = photos.find(p => p.id === id);

        if (!photo) {
          showStatus('Photo not found', 'error');
          return;
        }

        editingId = id;
        currentImage = photo.image;
        document.getElementById('title').value = photo.title || '';
        document.getElementById('description').value = photo.description || '';
        document.getElementById('date').value = photo.date || '';
        document.getElementById('order').value = photo.order || 0;
        document.getElementById('private').checked = photo.private === true;

        // Show image preview
        const uploadArea = document.getElementById('upload-area');
        uploadArea.innerHTML = `<img src="${photo.image}" alt="Preview">`;
        uploadArea.classList.add('has-image');

        document.getElementById('editor-heading').textContent = 'Edit Photo';
        document.getElementById('save-btn').textContent = 'Update';
        document.getElementById('delete-btn').style.display = 'inline-block';

        // Update URL without reload
        history.replaceState({}, '', '?edit=' + id);
      } catch (e) {
        showStatus('Failed to load photo', 'error');
      }
    }

    async function login() {
      const password = document.getElementById('password').value;
      const errorEl = document.getElementById('login-error');

      try {
        const res = await fetch('/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ password })
        });

        if (res.ok) {
          const data = await res.json();
          csrfToken = data.csrf_token;
          showEditor();
        } else {
          errorEl.textContent = 'Invalid password';
          errorEl.style.display = 'block';
        }
      } catch (e) {
        errorEl.textContent = 'Connection error';
        errorEl.style.display = 'block';
      }
    }

    function showEditor() {
      document.getElementById('login-form').style.display = 'none';
      document.getElementById('editor-container').style.display = 'block';
    }

    function handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        uploadFile(file);
      }
    }

    async function uploadFile(file) {
      const formData = new FormData();
      formData.append('file', file);

      try {
        showStatus('Uploading...', 'success');
        const res = await fetch('/api/upload.php', {
          method: 'POST',
          headers: { 'X-CSRF-Token': csrfToken },
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          currentImage = data.url;
          const uploadArea = document.getElementById('upload-area');
          uploadArea.innerHTML = `<img src="${data.url}" alt="Preview">`;
          uploadArea.classList.add('has-image');
          showStatus('Uploaded: ' + data.filename, 'success');
        } else {
          showStatus('Upload failed: ' + data.error, 'error');
        }
      } catch (e) {
        showStatus('Upload error: ' + e.message, 'error');
      }
    }

    async function savePhoto() {
      const title = document.getElementById('title').value.trim();
      const description = document.getElementById('description').value.trim();
      const date = document.getElementById('date').value;
      const order = parseInt(document.getElementById('order').value) || 0;
      const isPrivate = document.getElementById('private').checked;

      if (!currentImage) {
        showStatus('Please upload an image', 'error');
        return;
      }

      try {
        const isEditing = editingId !== null;
        const payload = isEditing
          ? { id: editingId, image: currentImage, title, description, date, order, private: isPrivate }
          : { image: currentImage, title, description, date, order, private: isPrivate };

        const res = await fetch('/api/photos.php', {
          method: isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
          // Redirect to view the photo
          const id = data.id || editingId;
          window.location.href = '/photos/?id=' + id;
        } else {
          showStatus('Error: ' + data.error, 'error');
        }
      } catch (e) {
        showStatus('Save error: ' + e.message, 'error');
      }
    }

    function newPhoto() {
      editingId = null;
      currentImage = null;
      document.getElementById('title').value = '';
      document.getElementById('description').value = '';
      document.getElementById('date').value = new Date().toISOString().split('T')[0];
      document.getElementById('order').value = '0';
      document.getElementById('private').checked = false;

      const uploadArea = document.getElementById('upload-area');
      uploadArea.innerHTML = '<p>Click or drag image here to upload</p>';
      uploadArea.classList.remove('has-image');

      document.getElementById('editor-heading').textContent = 'New Photo';
      document.getElementById('save-btn').textContent = 'Publish';
      document.getElementById('delete-btn').style.display = 'none';
      history.replaceState({}, '', '/write/photos.php');
    }

    async function deletePhoto() {
      if (!editingId) {
        showStatus('No photo selected to delete', 'error');
        return;
      }

      const title = document.getElementById('title').value || 'this photo';
      if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
        return;
      }

      try {
        const res = await fetch('/api/photos.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ id: editingId })
        });

        const data = await res.json();

        if (data.success) {
          // Redirect to photos list
          window.location.href = '/photos/';
        } else {
          showStatus('Error: ' + data.error, 'error');
        }
      } catch (e) {
        showStatus('Delete error: ' + e.message, 'error');
      }
    }

    async function logout() {
      await fetch('/api/auth.php', { method: 'DELETE' });
      location.reload();
    }

    function showStatus(message, type) {
      const el = document.getElementById('status');
      el.textContent = message;
      el.className = 'status ' + type;
      el.style.display = 'block';
      setTimeout(() => { el.style.display = 'none'; }, 5000);
    }
  </script>
</body>
</html>
