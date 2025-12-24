<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write - blogahrah</title>
  <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #1a1a1a;
      color: #e0e0e0;
      margin: 0;
      padding: 20px;
      min-height: 100vh;
    }
    .container { max-width: 900px; margin: 0 auto; }
    h1 { color: #00ff00; margin-bottom: 20px; }

    /* Login form */
    #login-form {
      max-width: 300px;
      margin: 100px auto;
      padding: 30px;
      background: #2a2a2a;
      border-radius: 8px;
    }
    #login-form input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #444;
      border-radius: 4px;
      background: #1a1a1a;
      color: #e0e0e0;
      font-size: 16px;
    }
    #login-form button {
      width: 100%;
      padding: 12px;
      background: #00ff00;
      color: #000;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
    }
    #login-form button:hover { background: #00cc00; }
    .error { color: #ff4444; margin-bottom: 15px; }

    /* Editor */
    #editor-container { display: none; }
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }
    .form-row input {
      flex: 1;
      padding: 12px;
      border: 1px solid #444;
      border-radius: 4px;
      background: #2a2a2a;
      color: #e0e0e0;
      font-size: 16px;
    }
    .form-row input[type="date"] { max-width: 200px; }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn-primary { background: #00ff00; color: #000; }
    .btn-primary:hover { background: #00cc00; }
    .btn-secondary { background: #444; color: #e0e0e0; }
    .btn-secondary:hover { background: #555; }

    /* Upload zone */
    .upload-zone {
      border: 2px dashed #444;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      margin-bottom: 15px;
      cursor: pointer;
      transition: border-color 0.2s;
    }
    .upload-zone:hover, .upload-zone.dragover {
      border-color: #00ff00;
    }
    .upload-zone input { display: none; }

    /* Toast UI Editor dark theme overrides */
    .toastui-editor-defaultUI {
      border-color: #444;
    }
    .toastui-editor-defaultUI-toolbar {
      background: #2a2a2a;
      border-color: #444;
    }
    .toastui-editor-toolbar-icons {
      background-position-y: -49px;
      filter: invert(0.8);
    }
    .toastui-editor-defaultUI-toolbar button {
      border-color: transparent;
    }
    .toastui-editor-defaultUI-toolbar button:hover {
      background: #444;
    }
    .toastui-editor-mode-switch {
      background: #2a2a2a;
      border-color: #444;
    }
    .toastui-editor-mode-switch .tab-item {
      background: #1a1a1a;
      color: #888;
      border-color: #444;
    }
    .toastui-editor-mode-switch .tab-item.active {
      background: #2a2a2a;
      color: #00ff00;
      border-color: #444;
    }
    .toastui-editor-ww-container,
    .toastui-editor-md-container {
      background: #2a2a2a;
    }
    .toastui-editor-contents,
    .toastui-editor-md-preview {
      background: #2a2a2a;
      color: #e0e0e0;
    }
    .toastui-editor-contents h1,
    .toastui-editor-contents h2,
    .toastui-editor-contents h3 {
      color: #00ff00;
      border-color: #444;
    }
    .toastui-editor-contents a {
      color: #66aaff;
    }
    .toastui-editor-contents pre,
    .toastui-editor-contents code {
      background: #1a1a1a;
      color: #e0e0e0;
    }
    .ProseMirror {
      color: #e0e0e0 !important;
    }
    .ProseMirror p,
    .ProseMirror li,
    .ProseMirror td,
    .ProseMirror th {
      color: #e0e0e0 !important;
    }
    .toastui-editor-ww-container .toastui-editor-contents p,
    .toastui-editor-ww-container .toastui-editor-contents {
      color: #e0e0e0 !important;
    }
    .toastui-editor-contents p {
      color: #e0e0e0 !important;
    }
    .toastui-editor-contents[contenteditable="true"] {
      color: #e0e0e0 !important;
    }
    .toastui-editor-contents[contenteditable="true"] * {
      color: inherit !important;
    }
    .toastui-editor-contents[contenteditable="true"] a {
      color: #66aaff !important;
    }
    .toastui-editor-contents[contenteditable="true"] h1,
    .toastui-editor-contents[contenteditable="true"] h2,
    .toastui-editor-contents[contenteditable="true"] h3 {
      color: #00ff00 !important;
    }
    .toastui-editor-md-splitter {
      background: #444;
    }
    .toastui-editor-popup {
      background: #2a2a2a;
      border-color: #444;
    }
    .toastui-editor-popup-body label {
      color: #e0e0e0;
    }
    .toastui-editor-popup-body input {
      background: #1a1a1a;
      border-color: #444;
      color: #e0e0e0;
    }
    .toastui-editor-dropdown-toolbar {
      background: #2a2a2a;
      border-color: #444;
    }
    #editor-wrapper {
      min-height: 400px;
    }

    .status { margin-top: 15px; padding: 10px; border-radius: 4px; }
    .status.success { background: #1a3a1a; color: #00ff00; }
    .status.error { background: #3a1a1a; color: #ff4444; }

    .back-link { color: #00ff00; text-decoration: none; }
    .back-link:hover { text-decoration: underline; }

    /* Post list for editing */
    .post-picker {
      background: #2a2a2a;
      border-radius: 8px;
      margin-bottom: 20px;
      overflow: hidden;
    }
    .post-picker-header {
      padding: 12px 15px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #444;
    }
    .post-picker-header:hover { background: #333; }
    .post-picker-header span { color: #888; }
    .post-picker-list {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }
    .post-picker.open .post-picker-list {
      max-height: 300px;
      overflow-y: auto;
    }
    .post-picker-item {
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 1px solid #333;
      display: flex;
      justify-content: space-between;
    }
    .post-picker-item:hover { background: #333; }
    .post-picker-item:last-child { border-bottom: none; }
    .post-picker-item .date { color: #666; font-size: 0.85em; }
  </style>
</head>
<body>
  <div class="container">
    <!-- Login Form -->
    <div id="login-form">
      <h1>Login</h1>
      <div id="login-error" class="error" style="display:none;"></div>
      <input type="password" id="password" placeholder="Password" autofocus>
      <button onclick="login()">Enter</button>
    </div>

    <!-- Editor (hidden until logged in) -->
    <div id="editor-container">
      <p><a href="/" class="back-link">&larr; Back to site</a> | <a href="/blogahrah/" class="back-link">View blog</a></p>

      <div class="post-picker" id="post-picker">
        <div class="post-picker-header" onclick="togglePostPicker()">
          Edit existing post <span id="picker-arrow">▼</span>
        </div>
        <div class="post-picker-list" id="post-picker-list"></div>
      </div>

      <h1 id="editor-heading">New Post</h1>

      <div class="form-row">
        <input type="text" id="title" placeholder="Post title">
        <input type="date" id="date">
      </div>

      <div class="upload-zone" id="upload-zone">
        <p>Drop images/videos here or click to upload</p>
        <input type="file" id="file-input" accept="image/*,video/*" multiple>
      </div>

      <div id="editor-wrapper"></div>

      <div class="actions">
        <button class="btn btn-primary" id="save-btn" onclick="savePost()">Publish</button>
        <button class="btn btn-secondary" onclick="newPost()">New Post</button>
        <button class="btn btn-secondary" onclick="logout()">Logout</button>
      </div>

      <div id="status" class="status" style="display:none;"></div>
    </div>
  </div>

  <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
  <script>
    let editor;
    let csrfToken = null;
    let editingSlug = null;
    let allPosts = [];

    // Check auth on load
    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('date').value = new Date().toISOString().split('T')[0];

      const res = await fetch('/api/auth.php');
      const data = await res.json();

      if (data.authenticated) {
        csrfToken = data.csrf_token;
        showEditor();
        await loadPostList();
        checkEditParam();
      }

      // Enter key to login
      document.getElementById('password').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') login();
      });
    });

    function checkEditParam() {
      const params = new URLSearchParams(window.location.search);
      const slug = params.get('edit');
      if (slug) {
        loadPost(slug);
      }
    }

    async function loadPostList() {
      try {
        const res = await fetch('/api/posts.php');
        allPosts = await res.json();
        renderPostPicker();
      } catch (e) {
        console.error('Failed to load posts', e);
      }
    }

    function renderPostPicker() {
      const list = document.getElementById('post-picker-list');
      if (allPosts.length === 0) {
        list.innerHTML = '<div class="post-picker-item" style="color:#666;">No posts yet</div>';
        return;
      }
      list.innerHTML = allPosts.map(post => `
        <div class="post-picker-item" onclick="loadPost('${post.slug}')">
          <span>${escapeHtml(post.title)}</span>
          <span class="date">${post.date}</span>
        </div>
      `).join('');
    }

    function togglePostPicker() {
      const picker = document.getElementById('post-picker');
      const arrow = document.getElementById('picker-arrow');
      picker.classList.toggle('open');
      arrow.textContent = picker.classList.contains('open') ? '▲' : '▼';
    }

    function loadPost(slug) {
      const post = allPosts.find(p => p.slug === slug);
      if (!post) {
        showStatus('Post not found', 'error');
        return;
      }

      editingSlug = slug;
      document.getElementById('title').value = post.title;
      document.getElementById('date').value = post.date;
      editor.setMarkdown(post.content);
      localStorage.removeItem('toastui-draft');

      document.getElementById('editor-heading').textContent = 'Edit Post';
      document.getElementById('save-btn').textContent = 'Update';

      // Update URL without reload
      history.replaceState({}, '', '?edit=' + slug);

      // Close picker
      document.getElementById('post-picker').classList.remove('open');
      document.getElementById('picker-arrow').textContent = '▼';
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
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

      if (!editor) {
        editor = new toastui.Editor({
          el: document.getElementById('editor-wrapper'),
          height: '400px',
          initialEditType: 'wysiwyg',
          previewStyle: 'vertical',
          usageStatistics: false,
          autofocus: false,
          events: {
            change: () => {
              // Autosave to localStorage
              localStorage.setItem('toastui-draft', editor.getMarkdown());
            }
          }
        });

        // Restore draft if exists
        const draft = localStorage.getItem('toastui-draft');
        if (draft) {
          editor.setMarkdown(draft);
        }
      }

      setupUpload();
    }

    function setupUpload() {
      const zone = document.getElementById('upload-zone');
      const input = document.getElementById('file-input');

      zone.addEventListener('click', () => input.click());

      zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.classList.add('dragover');
      });

      zone.addEventListener('dragleave', () => {
        zone.classList.remove('dragover');
      });

      zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
      });

      input.addEventListener('change', () => {
        handleFiles(input.files);
        input.value = '';
      });
    }

    async function handleFiles(files) {
      for (const file of files) {
        const formData = new FormData();
        formData.append('file', file);

        try {
          const res = await fetch('/api/upload.php', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData
          });

          const data = await res.json();

          if (data.success) {
            // Insert markdown at cursor position
            editor.insertText('\n' + data.markdown + '\n');
            showStatus('Uploaded: ' + data.filename, 'success');
          } else {
            showStatus('Upload failed: ' + data.error, 'error');
          }
        } catch (e) {
          showStatus('Upload error: ' + e.message, 'error');
        }
      }
    }

    async function savePost() {
      const title = document.getElementById('title').value.trim();
      const date = document.getElementById('date').value;
      const content = editor.getMarkdown().trim();

      if (!title) {
        showStatus('Please enter a title', 'error');
        return;
      }
      if (!content) {
        showStatus('Please write some content', 'error');
        return;
      }

      try {
        const isEditing = editingSlug !== null;
        const payload = isEditing
          ? { slug: editingSlug, title, date, content }
          : { title, date, content };

        const res = await fetch('/api/posts.php', {
          method: isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
          showStatus(isEditing ? 'Updated!' : 'Published: ' + data.filename, 'success');
          if (!isEditing) {
            newPost();
          }
          loadPostList(); // Refresh the post list
        } else {
          showStatus('Error: ' + data.error, 'error');
        }
      } catch (e) {
        showStatus('Save error: ' + e.message, 'error');
      }
    }

    function newPost() {
      editingSlug = null;
      document.getElementById('title').value = '';
      document.getElementById('date').value = new Date().toISOString().split('T')[0];
      editor.setMarkdown('');
      localStorage.removeItem('toastui-draft');
      document.getElementById('editor-heading').textContent = 'New Post';
      document.getElementById('save-btn').textContent = 'Publish';
      history.replaceState({}, '', '/write/');
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
