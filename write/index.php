<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Write - blogahrah</title>
  <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
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

    /* EasyMDE dark theme overrides */
    .EasyMDEContainer .CodeMirror {
      background: #2a2a2a;
      color: #e0e0e0;
      border-color: #444;
    }
    .EasyMDEContainer .editor-toolbar {
      background: #2a2a2a;
      border-color: #444;
    }
    .EasyMDEContainer .editor-toolbar button { color: #e0e0e0 !important; }
    .EasyMDEContainer .editor-toolbar button:hover { background: #444; }
    .EasyMDEContainer .editor-preview {
      background: #2a2a2a;
      color: #e0e0e0;
    }
    .EasyMDEContainer .editor-preview pre { background: #1a1a1a; }
    .EasyMDEContainer .cm-s-easymde .cm-header { color: #00ff00; }
    .EasyMDEContainer .cm-s-easymde .cm-link { color: #66aaff; }

    .status { margin-top: 15px; padding: 10px; border-radius: 4px; }
    .status.success { background: #1a3a1a; color: #00ff00; }
    .status.error { background: #3a1a1a; color: #ff4444; }

    .back-link { color: #00ff00; text-decoration: none; }
    .back-link:hover { text-decoration: underline; }
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
      <h1>New Post</h1>

      <div class="form-row">
        <input type="text" id="title" placeholder="Post title">
        <input type="date" id="date">
      </div>

      <div class="upload-zone" id="upload-zone">
        <p>Drop images/videos here or click to upload</p>
        <input type="file" id="file-input" accept="image/*,video/*" multiple>
      </div>

      <textarea id="editor"></textarea>

      <div class="actions">
        <button class="btn btn-primary" onclick="savePost()">Publish</button>
        <button class="btn btn-secondary" onclick="clearEditor()">Clear</button>
        <button class="btn btn-secondary" onclick="logout()">Logout</button>
      </div>

      <div id="status" class="status" style="display:none;"></div>
    </div>
  </div>

  <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
  <script>
    let easyMDE;

    // Check auth on load
    document.addEventListener('DOMContentLoaded', async () => {
      document.getElementById('date').value = new Date().toISOString().split('T')[0];

      const res = await fetch('/api/auth.php');
      const data = await res.json();

      if (data.authenticated) {
        showEditor();
      }

      // Enter key to login
      document.getElementById('password').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') login();
      });
    });

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

      if (!easyMDE) {
        easyMDE = new EasyMDE({
          element: document.getElementById('editor'),
          spellChecker: false,
          autosave: {
            enabled: true,
            uniqueId: 'blogahrah-draft',
            delay: 1000
          },
          toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'image', '|', 'preview', 'side-by-side', 'fullscreen', '|', 'guide']
        });
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
            body: formData
          });

          const data = await res.json();

          if (data.success) {
            // Insert markdown at cursor
            const pos = easyMDE.codemirror.getCursor();
            easyMDE.codemirror.replaceRange(data.markdown + '\n', pos);
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
      const content = easyMDE.value().trim();

      if (!title) {
        showStatus('Please enter a title', 'error');
        return;
      }
      if (!content) {
        showStatus('Please write some content', 'error');
        return;
      }

      try {
        const res = await fetch('/api/posts.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ title, date, content })
        });

        const data = await res.json();

        if (data.success) {
          showStatus('Published: ' + data.filename, 'success');
          clearEditor();
          localStorage.removeItem('smde_blogahrah-draft');
        } else {
          showStatus('Error: ' + data.error, 'error');
        }
      } catch (e) {
        showStatus('Save error: ' + e.message, 'error');
      }
    }

    function clearEditor() {
      document.getElementById('title').value = '';
      document.getElementById('date').value = new Date().toISOString().split('T')[0];
      easyMDE.value('');
      localStorage.removeItem('smde_blogahrah-draft');
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
