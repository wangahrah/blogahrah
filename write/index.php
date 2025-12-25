<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write - blogahrah</title>
  <link rel="shortcut icon" href="/img/favicon.ico">
  <link rel="stylesheet" href="/main.css">
  <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css">
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
    .form-row input {
      flex: 1;
      padding: 12px;
      border: 1px solid #333;
      background: black;
      color: #00FF00;
      font-size: 16px;
      font-family: monospace;
    }
    .form-row input:focus {
      outline: 1px solid #00FF00;
    }
    .form-row input[type="date"] { max-width: 200px; }
    .form-row input[type="date"]::-webkit-calendar-picker-indicator {
      filter: invert(1);
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

    /* Toast UI Editor dark theme overrides */
    .toastui-editor-defaultUI {
      border-color: #333;
    }
    .toastui-editor-defaultUI-toolbar {
      background: #111;
      border-color: #333;
    }
    .toastui-editor-toolbar-icons {
      background-position-y: -49px;
      filter: brightness(1.8);
    }
    .toastui-editor-defaultUI-toolbar button {
      border-color: transparent;
      opacity: 1;
    }
    .toastui-editor-defaultUI-toolbar button:hover {
      background: #333;
    }
    .toastui-editor-toolbar-group button {
      filter: brightness(1.8);
    }
    .toastui-editor-mode-switch {
      background: #111;
      border-color: #333;
    }
    .toastui-editor-mode-switch .tab-item {
      background: black;
      color: #888;
      border-color: #333;
    }
    .toastui-editor-mode-switch .tab-item.active {
      background: #111;
      color: #00ff00;
      border-color: #333;
    }
    .toastui-editor-ww-container,
    .toastui-editor-md-container {
      background: black;
    }
    .toastui-editor-contents,
    .toastui-editor-md-preview {
      background: black;
      color: #00FF00;
      font-family: monospace;
      font-size: 125%;
      line-height: 1.8em;
    }
    .toastui-editor-contents h1,
    .toastui-editor-contents h2,
    .toastui-editor-contents h3 {
      color: #00ff00;
      border-color: #333;
    }
    .toastui-editor-contents a {
      color: #00ff00;
    }
    .toastui-editor-contents pre {
      background: #111;
      color: #00FF00;
      padding: 15px;
      border-radius: 3px;
      border: 1px solid #333;
    }
    .toastui-editor-contents code {
      background: #111;
      color: #00FF00;
      font-family: monospace;
      font-size: 0.9em;
    }
    .toastui-editor-contents p code {
      padding: 2px 6px;
      border-radius: 3px;
    }
    .toastui-editor-contents blockquote {
      border-left: 3px solid #00ff00;
      margin-left: 0;
      padding-left: 20px;
      color: #888;
      font-style: italic;
    }
    .ProseMirror {
      color: #00FF00 !important;
      font-family: monospace !important;
      font-size: 125% !important;
      line-height: 1.8em !important;
    }
    .ProseMirror p,
    .ProseMirror li,
    .ProseMirror td,
    .ProseMirror th {
      color: #00FF00 !important;
    }
    .toastui-editor-ww-container .toastui-editor-contents p,
    .toastui-editor-ww-container .toastui-editor-contents {
      color: #00FF00 !important;
    }
    .toastui-editor-contents p {
      color: #00FF00 !important;
    }
    .toastui-editor-contents[contenteditable="true"] {
      color: #00FF00 !important;
    }
    .toastui-editor-contents[contenteditable="true"] * {
      color: inherit !important;
    }
    .toastui-editor-contents[contenteditable="true"] a {
      color: #00ff00 !important;
    }
    .toastui-editor-contents[contenteditable="true"] h1,
    .toastui-editor-contents[contenteditable="true"] h2,
    .toastui-editor-contents[contenteditable="true"] h3 {
      color: #00ff00 !important;
    }
    .toastui-editor-contents img,
    .toastui-editor-contents video {
      display: block;
      max-width: 100%;
      height: auto;
      border-radius: 3px;
      margin: 20px auto;
      border: 1px solid #333;
    }
    .toastui-editor-md-splitter {
      background: #333;
    }
    .toastui-editor-popup {
      background: black;
      border-color: #333;
    }
    .toastui-editor-popup-body label {
      color: #00FF00;
    }
    .toastui-editor-popup-body input {
      background: black;
      border-color: #333;
      color: #00FF00;
    }
    .toastui-editor-dropdown-toolbar {
      background: #111;
      border-color: #333;
    }
    #editor-wrapper {
      min-height: 300px;
    }

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

      .form-row input[type="date"] {
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

      #editor-wrapper {
        min-height: 250px;
      }

      .toastui-editor-defaultUI {
        min-height: 300px;
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

      .form-row input {
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
        <h1 id="editor-heading">New Post</h1>

        <div class="form-row">
          <input type="text" id="title" placeholder="Post title">
          <input type="date" id="date">
        </div>

        <div class="form-row" style="justify-content: flex-start;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: monospace;">
            <input type="checkbox" id="private" style="width: auto; accent-color: #00ff00;">
            <span>Make private (only visible when logged in)</span>
          </label>
        </div>

        <div id="editor-wrapper"></div>

        <div class="actions">
          <button class="btn btn-primary" id="save-btn" onclick="savePost()">Publish</button>
          <button class="btn btn-secondary" onclick="newPost()">New Post</button>
          <button class="btn btn-danger" id="delete-btn" onclick="deletePost()" style="display:none;">Delete</button>
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
      <li><a href="https://www.linkedin.com/in/michaelanfang/" target="_blank">linkedin</a></li>
      <li><a href="https://anarchyincubator.com/" target="_blank">anarchy incubator</a></li>
      <li><a href="https://divinemetaphor.com/" target="_blank">divine metaphor</a></li>
      <li><a href="/legal.html">legal</a></li>
    </ul>
  </footer>

  <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
  <script>
    let editor;
    let csrfToken = null;
    let editingSlug = null;

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
    });

    function checkEditParam() {
      const params = new URLSearchParams(window.location.search);
      const slug = params.get('edit');
      if (slug) {
        loadPost(slug);
      }
    }

    async function loadPost(slug) {
      try {
        const res = await fetch('/api/posts.php');
        const posts = await res.json();
        const post = posts.find(p => p.slug === slug);

        if (!post) {
          showStatus('Post not found', 'error');
          return;
        }

        editingSlug = slug;
        document.getElementById('title').value = post.title;
        document.getElementById('date').value = post.date;
        document.getElementById('private').checked = post.private === true || post.private === 'true';
        editor.setMarkdown(post.content);
        localStorage.removeItem('toastui-draft');

        document.getElementById('editor-heading').textContent = 'Edit Post';
        document.getElementById('save-btn').textContent = 'Update';
        document.getElementById('delete-btn').style.display = 'inline-block';

        // Update URL without reload
        history.replaceState({}, '', '?edit=' + slug);
      } catch (e) {
        showStatus('Failed to load post', 'error');
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

      if (!editor) {
        editor = new toastui.Editor({
          el: document.getElementById('editor-wrapper'),
          height: '60vh',
          initialEditType: 'wysiwyg',
          previewStyle: 'vertical',
          usageStatistics: false,
          autofocus: false,
          events: {
            change: () => {
              // Autosave to localStorage
              localStorage.setItem('toastui-draft', editor.getMarkdown());
            }
          },
          hooks: {
            addImageBlobHook: async (blob, callback) => {
              const formData = new FormData();
              formData.append('file', blob);

              try {
                const res = await fetch('/api/upload.php', {
                  method: 'POST',
                  headers: { 'X-CSRF-Token': csrfToken },
                  body: formData
                });

                const data = await res.json();

                if (data.success) {
                  callback(data.url, blob.name);
                  showStatus('Uploaded: ' + data.filename, 'success');
                } else {
                  showStatus('Upload failed: ' + data.error, 'error');
                }
              } catch (e) {
                showStatus('Upload error: ' + e.message, 'error');
              }
            }
          }
        });

        // Restore draft if exists
        const draft = localStorage.getItem('toastui-draft');
        if (draft) {
          editor.setMarkdown(draft);
        }
      }
    }

    async function savePost() {
      const title = document.getElementById('title').value.trim();
      const date = document.getElementById('date').value;
      const content = editor.getMarkdown().trim();
      const isPrivate = document.getElementById('private').checked;

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
          ? { slug: editingSlug, title, date, content, private: isPrivate }
          : { title, date, content, private: isPrivate };

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
          localStorage.removeItem('toastui-draft');
          // Redirect to view the post
          const slug = data.slug || editingSlug;
          window.location.href = '/blogahrah/?p=' + slug;
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
      document.getElementById('private').checked = false;
      editor.setMarkdown('');
      localStorage.removeItem('toastui-draft');
      document.getElementById('editor-heading').textContent = 'New Post';
      document.getElementById('save-btn').textContent = 'Publish';
      document.getElementById('delete-btn').style.display = 'none';
      history.replaceState({}, '', '/write/');
    }

    async function deletePost() {
      if (!editingSlug) {
        showStatus('No post selected to delete', 'error');
        return;
      }

      const title = document.getElementById('title').value;
      if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis action cannot be undone.`)) {
        return;
      }

      try {
        const res = await fetch('/api/posts.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ slug: editingSlug })
        });

        const data = await res.json();

        if (data.success) {
          localStorage.removeItem('toastui-draft');
          // Redirect to blog list
          window.location.href = '/blogahrah/';
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
