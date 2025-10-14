<?php 
include __DIR__ . '/admin-components/admin-header.php';
include __DIR__ . '/includes/db-config.php';
include __DIR__ . '/functions/function.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("CSRF token validation failed");
    }
    
    $action = $_POST['action'] ?? '';
    $blog_id = $_POST['blog_id'] ?? '';
    
    try {
        switch($action) {
            case 'create':
            case 'update':
                $title = $conn->real_escape_string($_POST['title'] ?? '');
                $content = $conn->real_escape_string($_POST['content'] ?? '');
                $excerpt = $conn->real_escape_string($_POST['excerpt'] ?? '');
                $featured_image = $conn->real_escape_string($_POST['featured_image'] ?? '');
                $status = $conn->real_escape_string($_POST['status'] ?? 'draft');
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $read_time = intval($_POST['read_time'] ?? 5);
                $meta_description = $conn->real_escape_string($_POST['meta_description'] ?? '');
                $slug = $conn->real_escape_string($_POST['slug'] ?? '');
                
                // Generate slug from title if empty
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                }
                
                if ($action === 'create') {
                    $stmt = $conn->prepare("INSERT INTO tbl_blog (title, content, excerpt, featured_image, author_id, status, is_featured, read_time, meta_description, slug, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssssisiss", $title, $content, $excerpt, $featured_image, $_SESSION['admin_id'], $status, $is_featured, $read_time, $meta_description, $slug);
                } else {
                    $stmt = $conn->prepare("UPDATE tbl_blog SET title=?, content=?, excerpt=?, featured_image=?, status=?, is_featured=?, read_time=?, meta_description=?, slug=?, updated_at=NOW() WHERE blog_id=?");
                    $stmt->bind_param("sssssiissi", $title, $content, $excerpt, $featured_image, $status, $is_featured, $read_time, $meta_description, $slug, $blog_id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = $action === 'create' ? 'Blog post created successfully!' : 'Blog post updated successfully!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    throw new Exception("Failed to save blog post: " . $stmt->error);
                }
                $stmt->close();
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM tbl_blog WHERE blog_id = ?");
                $stmt->bind_param("i", $blog_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Blog post deleted successfully!';
                    $_SESSION['message_type'] = 'success';
                }
                $stmt->close();
                break;
        }
        
        // Redirect to avoid form resubmission
        header("Location: blog.php");
        exit;
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all blog posts
try {
    $blog_posts = [];
    $result = $conn->query("
        SELECT b.*, a.username as author_name 
        FROM tbl_blog b 
        LEFT JOIN tbl_admin a ON b.author_id = a.id 
        ORDER BY b.created_at DESC
    ");
    
    if ($result) {
        $blog_posts = $result->fetch_all(MYSQLI_ASSOC);
    }
    
} catch (Exception $e) {
    $error = "Error loading blog posts: " . $e->getMessage();
}

$conn->close();
?>

<!-- BLOG MANAGEMENT -->
<section id="view-blog" class="view">
    <div class="page-header">
        <h1>Blog Management</h1>
        <button class="btn primary" onclick="openBlogEditor()">
            <i class="fas fa-plus"></i> New Blog Post
        </button>
    </div>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'error'; ?>" 
             style="padding: 12px; margin-bottom: 16px; border-radius: 8px; background: var(--<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?>); color: white;">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Blog Posts Table -->
    <div class="card">
        <div class="card-header">
            <h3>Blog Posts</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($blog_posts)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Author</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                        <?php if ($post['is_featured']): ?>
                                            <span class="badge" style="background: var(--primary); color: white; margin-left: 8px;">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($post['status']); ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $post['is_featured'] ? 'Yes' : 'No'; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn small" onclick="editBlogPost(<?php echo $post['blog_id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn small danger" onclick="deleteBlogPost(<?php echo $post['blog_id']; ?>, '<?php echo htmlspecialchars($post['title']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-blog" style="font-size: 3rem; color: var(--muted); margin-bottom: 1rem;"></i>
                    <h3>No Blog Posts Yet</h3>
                    <p>Get started by creating your first blog post.</p>
                    <button class="btn primary" onclick="openBlogEditor()">
                        <i class="fas fa-plus"></i> Create First Post
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- BLOG EDITOR MODAL -->
<dialog id="blogEditorModal">
    <div class="modal-header">
        <strong id="blogEditorTitle">New Blog Post</strong>
        <button class="btn" onclick="closeBlogEditor()">✕</button>
    </div>
    <div class="modal-body">
        <form id="blogEditorForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" id="blog_id" name="blog_id">
            <input type="hidden" id="action" name="action" value="create">
            
            <div class="form-grid">
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="field">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" placeholder="auto-generated-from-title">
                </div>
                
                <div class="field">
                    <label for="read_time">Read Time (minutes)</label>
                    <input type="number" id="read_time" name="read_time" value="5" min="1" max="60">
                </div>
                
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                
                <div class="field">
                    <label for="is_featured" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1">
                        Featured Post
                    </label>
                </div>
                
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="featured_image">Featured Image URL</label>
                    <input type="url" id="featured_image" name="featured_image" placeholder="https://images.unsplash.com/photo-...">
                </div>
                
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="excerpt">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="3" placeholder="Brief description of the blog post..."></textarea>
                </div>
                
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="2" placeholder="SEO description..."></textarea>
                </div>
                
                <div class="field" style="grid-column: 1 / -1;">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="12" required placeholder="Write your blog post content here..."></textarea>
                </div>
            </div>
            
            <div style="grid-column: 1 / -1; display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px;">
                <button class="btn" type="button" onclick="closeBlogEditor()">Cancel</button>
                <button class="btn primary" type="submit" id="blogSaveBtn">Create Post</button>
            </div>
        </form>
    </div>
</dialog>

<script>
// Blog Editor Functions
function openBlogEditor(blogId = null) {
    const modal = document.getElementById('blogEditorModal');
    const title = document.getElementById('blogEditorTitle');
    const form = document.getElementById('blogEditorForm');
    const actionInput = document.getElementById('action');
    const saveBtn = document.getElementById('blogSaveBtn');
    
    if (blogId) {
        title.textContent = 'Edit Blog Post';
        saveBtn.textContent = 'Update Post';
        actionInput.value = 'update';
        loadBlogPost(blogId);
    } else {
        title.textContent = 'New Blog Post';
        saveBtn.textContent = 'Create Post';
        actionInput.value = 'create';
        form.reset();
        document.getElementById('blog_id').value = '';
    }
    
    modal.showModal();
}

function closeBlogEditor() {
    document.getElementById('blogEditorModal').close();
}

function loadBlogPost(blogId) {
    // In a real implementation, this would fetch the post data via AJAX
    // For now, we'll use a simple approach - the form will be populated on page load
    // This would typically be implemented with a separate API endpoint
    console.log('Loading blog post:', blogId);
}

function editBlogPost(blogId) {
    openBlogEditor(blogId);
    // Note: In a production environment, you would fetch the blog post data via AJAX
    // and populate the form fields. For this example, we're using the basic structure.
}

function deleteBlogPost(blogId, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'blog.php';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token']; ?>';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'blog_id';
        idInput.value = blogId;
        
        form.appendChild(csrfInput);
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-generate slug from title
document.getElementById('title').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value) {
        const slug = this.value.toLowerCase()
            .trim()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        slugField.value = slug;
    }
});

// Form submission
document.getElementById('blogEditorForm').addEventListener('submit', function(e) {
    // Basic validation
    const title = document.getElementById('title').value.trim();
    const content = document.getElementById('content').value.trim();
    
    if (!title || !content) {
        e.preventDefault();
        alert('Please fill in all required fields (Title and Content).');
        return;
    }
});
</script>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h1 {
    margin: 0;
    color: var(--text);
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.data-table th {
    background: var(--panel);
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-published {
    background: var(--success);
    color: white;
}

.status-draft {
    background: var(--muted);
    color: var(--text);
}

.status-featured {
    background: var(--primary);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn.small {
    padding: 6px 12px;
    font-size: 0.875rem;
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--muted);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.field {
    display: flex;
    flex-direction: column;
}

.field label {
    margin-bottom: 6px;
    font-weight: 500;
    color: var(--text);
}

.field input,
.field select,
.field textarea {
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--panel);
    color: var(--text);
    font-family: inherit;
}

.field textarea {
    resize: vertical;
    min-height: 100px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.alert-success {
    background: var(--success);
    color: white;
}

.alert-error {
    background: var(--danger);
    color: white;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
</style>

</main>
</div>

<?php include __DIR__ . '/admin-components/admin-footer.php'; ?>