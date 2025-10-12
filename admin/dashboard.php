<?php 
include __DIR__ . '/admin-components/admin-header.php';
?>
      <main class="main">
        <!-- DASHBOARD -->
        <section id="view-dashboard" class="view">
          <div class="cards" id="statsCards"></div>
          <div class="grid-2" style="margin-top:16px">
            <div class="card">
              <h3>Recent Activity</h3>
              <div id="activity"></div>
            </div>
            <div class="card">
              <h3>Quick Add</h3>
              <div class="form">
                <div class="field"><label>Type</label>
                  <select id="quickType">
                    <option value="packages">Package</option>
                    <option value="services">Service</option>
                    <option value="blog">Blog Post</option>
                    <option value="products">Product</option>
                  </select>
                </div>
                <div class="field">
                  <label>Name/Title</label>
                  <input id="quickName"/>
                </div>
                <div class="field" style="grid-column:1 / -1">
                  <label>Description</label>
                  <textarea id="quickDesc"></textarea>
                </div>
                <div style="grid-column:1 / -1;display:flex;gap:8px;justify-content:flex-end">
                  <button class="btn" id="quickClear">Clear</button>
                  <button class="btn primary" id="quickAdd">Add</button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- GENERIC LIST VIEWS TEMPLATE MOUNT POINTS -->
        <section id="view-packages" class="view" hidden></section>
        <section id="view-services" class="view" hidden></section>
        <section id="view-blog" class="view" hidden></section>
        <section id="view-bookings" class="view" hidden></section>
        <section id="view-products" class="view" hidden></section>
        <section id="view-inquiries" class="view" hidden></section>
        <section id="view-reviews" class="view" hidden></section>

        <!-- SETTINGS -->
        <section id="view-settings" class="view" hidden>
          <div class="card">
            <h3>Profile & Page Settings</h3>
            <div class="form" id="settingsForm">
              <div class="field"><label>Admin display name</label><input id="set-adminName"></div>
              <div class="field"><label>Page name</label><input id="set-pageName"></div>
              <div class="field" style="grid-column:1 / -1"><label>About us</label><textarea id="set-about"></textarea></div>
              <div class="field" style="grid-column:1 / -1"><label>Privacy & Policy</label><textarea id="set-privacy"></textarea></div>
              <div style="grid-column:1 / -1;display:flex;gap:8px;justify-content:flex-end">
                <button class="btn" id="settingsExport">Export JSON</button>
                <label class="btn" for="settingsImport">Import JSON</label>
                <input type="file" id="settingsImport" accept="application/json" hidden />
                <button class="btn primary" id="settingsSave">Save</button>
              </div>
            </div>
          </div>
        </section>
      </main>
    </section>
  </div>

  <!-- MODAL: Editor -->
  <dialog id="editorModal">
    <div class="modal-header">
      <strong id="editorTitle">New Item</strong>
      <button class="btn" onclick="editor.close()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form" id="editorForm">
        <div class="field"><label>Name / Title</label><input id="ed-name" required></div>
        <div class="field"><label>Status</label>
          <select id="ed-status">
            <option value="live">Live</option>
            <option value="draft">Draft</option>
            <option value="unread">Unread</option>
          </select>
        </div>
        <div class="field" style="grid-column:1 / -1"><label>Description</label><textarea id="ed-desc"></textarea></div>
        <div class="field" id="ed-extra" style="grid-column:1 / -1"></div>
        <div style="grid-column:1 / -1;display:flex;gap:8px;justify-content:flex-end">
          <button class="btn" id="ed-cancel">Cancel</button>
          <button class="btn primary" id="ed-save">Save</button>
        </div>
      </div>
    </div>
  </dialog>

  <!-- Toast -->
  <div id="toast" style="position:fixed;bottom:20px;right:20px;padding:10px 14px;border-radius:12px;background:#111827;border:1px solid var(--border);display:none"></div>

<?php include __DIR__ . '/admin-components/admin-footer.php';?>