<?php

namespace App\Controllers\Admin;

use App\Libraries\MediaLibrary;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Model;

/**
 * Config-driven list / create / edit / delete / toggle screens.
 * A module only declares its model, list columns and form fields; see e.g. Faqs.php.
 *
 * Field types: text, email, url, number, textarea, richtext, select, checkbox, image,
 *              datetime, slug, password.
 * Column types: text (default), toggle, image, date, money, badge.
 */
abstract class CrudController extends AdminController
{
    protected string $modelName = '';
    protected string $path      = '';          // admin/faqs
    protected string $plural    = 'Items';
    protected string $singular  = 'Item';
    protected string $group     = '';          // breadcrumb group label
    protected array $columns    = [];          // field => label | [label, type]
    protected array $fields     = [];          // field => [label, type, rules, ...]
    protected array $searchable = [];
    protected string $orderBy   = 'id';
    protected string $orderDir  = 'DESC';
    protected int $perPage      = 20;
    protected bool $canCreate   = true;
    protected bool $canDelete   = true;
    protected string $intro     = '';
    protected string $mediaFolder = 'general';

    protected function model(): Model
    {
        return model($this->modelName);
    }

    /** Hook: adjust the list query (joins, fixed filters). */
    protected function listQuery(Model $model): void
    {
    }

    /** Hook: resolve dynamic select options etc. before the form is shown / validated. */
    protected function fields(?array $row = null): array
    {
        return $this->fields;
    }

    /** Hook: last chance to change the data before it is saved. */
    protected function beforeSave(array $data, ?int $id): array
    {
        return $data;
    }

    protected function afterSave(int $id, array $data): void
    {
    }

    /** Hook: return a message to block deletion. */
    protected function blockDelete(array $row): ?string
    {
        return null;
    }

    /** Hook: query string appended to redirects (e.g. the parent menu). */
    protected function contextQuery(): string
    {
        return '';
    }

    public function index(): string
    {
        $model = $this->model();
        $this->listQuery($model);
        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '' && $this->searchable) {
            $model->groupStart();
            foreach ($this->searchable as $i => $col) {
                $i === 0 ? $model->like($col, $q) : $model->orLike($col, $q);
            }
            $model->groupEnd();
        }
        // An empty direction means $orderBy is a raw SQL expression (used for tree ordering).
        $this->orderDir === '' ? $model->orderBy($this->orderBy, '', false) : $model->orderBy($this->orderBy, $this->orderDir);
        $rows = $model->paginate($this->perPage, 'admin');

        return $this->render('admin/crud/index', [
            'title'       => $this->plural,
            'breadcrumbs' => array_filter([[$this->group, null], [$this->plural, null]], static fn ($b) => $b[0] !== ''),
            'rows'        => $rows,
            'pager'       => $model->pager,
            'columns'     => $this->columns,
            'path'        => $this->path,
            'singular'    => $this->singular,
            'plural'      => $this->plural,
            'q'           => $q,
            'searchable'  => (bool) $this->searchable,
            'canCreate'   => $this->canCreate,
            'canDelete'   => $this->canDelete,
            'intro'       => $this->intro,
            'context'     => $this->contextQuery(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form($this->find($id));
    }

    protected function form(?array $row): string
    {
        return $this->render('admin/crud/form', [
            'title'       => ($row ? 'Edit ' : 'Add ') . strtolower($this->singular),
            'breadcrumbs' => array_filter([[$this->group, null], [$this->plural, site_url($this->path) . $this->contextQuery()], [$row ? 'Edit' : 'Add', null]], static fn ($b) => $b[0] !== ''),
            'row'         => $row,
            'fields'      => $this->fields($row),
            'path'        => $this->path,
            'singular'    => $this->singular,
            'mediaFolder' => $this->mediaFolder,
            'context'     => $this->contextQuery(),
        ]);
    }

    public function store()
    {
        return $this->save(null);
    }

    public function update(int $id)
    {
        $this->find($id);

        return $this->save($id);
    }

    protected function save(?int $id)
    {
        $fields = $this->fields($id ? $this->find($id) : null);
        $rules  = [];
        foreach ($fields as $name => $f) {
            $rule = $f['rules'] ?? 'permit_empty';
            if ($id) {
                $rule = str_replace('{id}', (string) $id, $rule);
            } else {
                $rule = str_replace(',id,{id}', '', $rule);
            }
            if (($f['type'] ?? 'text') === 'password' && $id) {
                $rule = 'permit_empty|' . str_replace('required|', '', $rule);
            }
            $rules[$name] = ['label' => $f['label'], 'rules' => $rule];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        foreach ($fields as $name => $f) {
            $type  = $f['type'] ?? 'text';
            $value = $this->request->getPost($name);
            switch ($type) {
                case 'checkbox':
                    $data[$name] = $value ? 1 : 0;
                    break;

                case 'richtext':
                    $data[$name] = rich((string) $value);
                    break;

                case 'number':
                    $data[$name] = ($value === '' || $value === null) ? ($f['nullable'] ?? false ? null : 0) : $value;
                    break;

                case 'slug':
                    $source      = (string) ($value ?: $this->request->getPost($f['from'] ?? 'name'));
                    $data[$name] = empty($f['keep']) ? slugify($source) : trim($source);
                    break;

                case 'password':
                    if ((string) $value !== '') {
                        $data[$f['column'] ?? 'password_hash'] = password_hash((string) $value, PASSWORD_DEFAULT);
                    }
                    break;

                case 'image':
                    $file = $this->request->getFile($name . '_upload');
                    if ($file && $file->isValid()) {
                        try {
                            $media       = (new MediaLibrary())->store($file, $this->mediaFolder);
                            $data[$name] = $media['path'];
                        } catch (\RuntimeException $e) {
                            return redirect()->back()->withInput()->with('error', $e->getMessage());
                        }
                    } else {
                        $data[$name] = trim((string) $value) ?: null;
                    }
                    break;

                case 'datetime':
                    $data[$name] = $value ? date('Y-m-d H:i:s', strtotime((string) $value)) : null;
                    break;

                default:
                    $value       = is_string($value) ? trim($value) : $value;
                    $data[$name] = ($value === '' && ($f['nullable'] ?? true)) ? null : $value;
            }
        }

        $data  = $this->beforeSave($data, $id);
        $model = $this->model();

        try {
            if ($id) {
                $model->update($id, $data);
            } else {
                $id = (int) $model->insert($data, true);
            }
        } catch (\Throwable $e) {
            log_message('error', static::class . ' save failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not save. ' . (str_contains($e->getMessage(), 'Duplicate') ? 'That value is already in use.' : 'Please check the form and try again.'));
        }

        $this->afterSave($id, $data);
        $this->flushCache();

        return redirect()->to(site_url($this->path) . $this->contextQuery())->with('success', $this->singular . ' saved.');
    }

    public function delete(int $id)
    {
        $row = $this->find($id);
        if (! $this->canDelete) {
            return redirect()->back()->with('error', 'This item cannot be deleted.');
        }
        if ($reason = $this->blockDelete($row)) {
            return redirect()->back()->with('error', $reason);
        }

        try {
            $this->model()->delete($id);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'This item is in use and cannot be deleted.');
        }
        $this->flushCache();

        return redirect()->to(site_url($this->path) . $this->contextQuery())->with('success', $this->singular . ' deleted.');
    }

    /** Flips a boolean column from the list (only columns declared as type "toggle"). */
    public function toggle(int $id, string $field)
    {
        $row = $this->find($id);
        $col = $this->columns[$field] ?? null;
        if (! is_array($col) || ($col[1] ?? '') !== 'toggle') {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->model()->update($id, [$field => $row[$field] ? 0 : 1]);
        $this->flushCache();

        return redirect()->back()->with('success', $this->singular . ' updated.');
    }

    protected function find(int $id): array
    {
        $row = $this->model()->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $row;
    }
}
