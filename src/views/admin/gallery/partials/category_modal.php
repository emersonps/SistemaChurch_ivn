<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gerenciar Categorias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="categoryModalError" class="alert alert-danger d-none"></div>
                <div class="input-group mb-3">
                    <input type="text" id="newCategoryName" class="form-control" placeholder="Nova categoria">
                    <button type="button" class="btn btn-primary" id="addCategoryBtn"><i class="fas fa-plus"></i></button>
                </div>
                <ul class="list-group" id="categoryList">
                    <?php foreach ($categoryRows as $cat): ?>
                        <li class="list-group-item d-flex align-items-center gap-2" data-id="<?= (int)$cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>">
                            <input type="text" class="form-control form-control-sm category-name-input" value="<?= htmlspecialchars($cat['name']) ?>">
                            <button type="button" class="btn btn-sm btn-outline-primary category-save-btn" title="Salvar"><i class="fas fa-check"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger category-delete-btn" title="Excluir"><i class="fas fa-trash"></i></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('categoryModal');
    var list = document.getElementById('categoryList');
    var errorBox = document.getElementById('categoryModalError');
    var csrfToken = document.querySelector('input[name="csrf_token"]').value;
    var categorySelect = document.querySelector('select[name="category"]');

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    }

    function clearError() {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }

    function postJson(url, params) {
        var body = new URLSearchParams(params);
        body.set('csrf_token', csrfToken);
        return fetch(url, { method: 'POST', body: body })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        throw new Error(data.error || 'Ocorreu um erro.');
                    }
                    return data;
                });
            });
    }

    function addOption(name) {
        var option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        categorySelect.appendChild(option);
    }

    function buildCategoryRow(id, name) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center gap-2';
        li.dataset.id = id;
        li.dataset.name = name;

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm category-name-input';
        input.value = name;

        var saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'btn btn-sm btn-outline-primary category-save-btn';
        saveBtn.title = 'Salvar';
        saveBtn.innerHTML = '<i class="fas fa-check"></i>';

        var deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-sm btn-outline-danger category-delete-btn';
        deleteBtn.title = 'Excluir';
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';

        li.appendChild(input);
        li.appendChild(saveBtn);
        li.appendChild(deleteBtn);
        return li;
    }

    document.getElementById('addCategoryBtn').addEventListener('click', function () {
        clearError();
        var input = document.getElementById('newCategoryName');
        var name = input.value.trim();
        if (name === '') {
            return;
        }

        postJson('/admin/gallery/categories', { name: name }).then(function (data) {
            list.appendChild(buildCategoryRow(data.category.id, data.category.name));
            addOption(data.category.name);
            input.value = '';
        }).catch(function (err) {
            showError(err.message);
        });
    });

    list.addEventListener('click', function (event) {
        var saveBtn = event.target.closest('.category-save-btn');
        var deleteBtn = event.target.closest('.category-delete-btn');

        if (saveBtn) {
            clearError();
            var li = saveBtn.closest('li');
            var id = li.dataset.id;
            var oldName = li.dataset.name;
            var newName = li.querySelector('.category-name-input').value.trim();
            if (newName === '' || newName === oldName) {
                return;
            }

            postJson('/admin/gallery/categories/rename/' + id, { name: newName }).then(function (data) {
                li.dataset.name = data.category.name;
                var option = Array.prototype.find.call(categorySelect.options, function (opt) {
                    return opt.value === oldName;
                });
                if (option) {
                    option.value = data.category.name;
                    option.textContent = data.category.name;
                }
            }).catch(function (err) {
                showError(err.message);
            });
        }

        if (deleteBtn) {
            clearError();
            var liToDelete = deleteBtn.closest('li');
            if (!confirm('Excluir a categoria "' + liToDelete.dataset.name + '"?')) {
                return;
            }

            postJson('/admin/gallery/categories/delete/' + liToDelete.dataset.id, {}).then(function () {
                var option = Array.prototype.find.call(categorySelect.options, function (opt) {
                    return opt.value === liToDelete.dataset.name;
                });
                if (option) {
                    option.remove();
                }
                liToDelete.remove();
            }).catch(function (err) {
                showError(err.message);
            });
        }
    });

    modal.addEventListener('hidden.bs.modal', clearError);
});
</script>
