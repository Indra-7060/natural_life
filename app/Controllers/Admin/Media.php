<?php

namespace App\Controllers\Admin;

use App\Libraries\MediaLibrary;
use CodeIgniter\Exceptions\PageNotFoundException;

class Media extends AdminController
{
    protected string $module = 'media';

    public function index(): string
    {
        [$rows, $pager, $q, $folder] = $this->query(36);
        $library = new MediaLibrary();
        foreach ($rows as &$r) {
            $r['usages'] = $library->usages($r['path']);
        }

        return $this->render('admin/media/index', [
            'title' => 'Media library', 'breadcrumbs' => [['Library', null], ['Media library', null]],
            'rows' => $rows, 'pager' => $pager, 'q' => $q, 'folder' => $folder, 'folders' => MediaLibrary::FOLDERS,
        ]);
    }

    /** JSON list for the image picker used on every form. */
    public function picker()
    {
        [$rows, $pager] = $this->query(48);

        return $this->response->setJSON([
            'items' => array_map(static fn ($r) => [
                'path' => $r['path'], 'name' => $r['file_name'], 'thumb' => media_url($r['path'], 'sm'), 'folder' => $r['folder'],
                'size' => $r['width'] ? $r['width'] . '×' . $r['height'] : '',
            ], $rows),
            'more' => $pager->hasMore('admin'),
        ]);
    }

    public function upload()
    {
        $folder  = (string) $this->request->getPost('folder');
        $files   = $this->request->getFileMultiple('files') ?? [];
        $library = new MediaLibrary();
        $added   = [];
        $errors  = [];

        foreach ($files as $file) {
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            try {
                $added[] = $library->store($file, $folder);
            } catch (\RuntimeException $e) {
                $errors[] = $file->getClientName() . ': ' . $e->getMessage();
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($added ? 200 : 422)->setJSON([
                'ok' => (bool) $added, 'message' => $errors ? implode(' ', $errors) : count($added) . ' image(s) uploaded.', 'csrf' => csrf_hash(),
                'items' => array_map(static fn ($m) => ['path' => $m['path'], 'name' => $m['file_name'], 'thumb' => media_url($m['path'], 'sm')], $added),
            ]);
        }
        if (! $added && ! $errors) {
            $errors[] = 'Choose at least one image to upload.';
        }
        $redirect = redirect()->to(site_url('admin/media') . ($folder ? '?folder=' . rawurlencode($folder) : ''));

        return $errors ? $redirect->with('error', implode(' ', $errors)) : $redirect->with('success', count($added) . ' image(s) uploaded.');
    }

    public function update(int $id)
    {
        if (! model('MediaModel')->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }
        model('MediaModel')->update($id, ['alt_text' => mb_substr(trim((string) $this->request->getPost('alt_text')), 0, 255) ?: null]);

        return redirect()->back()->with('success', 'Description saved.');
    }

    /** Deletes a file only when nothing on the site still uses it. */
    public function delete(int $id)
    {
        $media = model('MediaModel')->find($id);
        if (! $media) {
            throw PageNotFoundException::forPageNotFound();
        }
        $library = new MediaLibrary();
        if ($used = $library->usages($media['path'])) {
            return redirect()->back()->with('error', 'This image is in use (' . implode(', ', $used) . '). Replace it there first, then delete it.');
        }
        $library->delete($media);

        return redirect()->back()->with('success', 'Image deleted.');
    }

    private function query(int $perPage): array
    {
        $model  = model('MediaModel');
        $q      = trim((string) $this->request->getGet('q'));
        $folder = (string) $this->request->getGet('folder');
        if ($q !== '') {
            $model->groupStart()->like('file_name', $q)->orLike('alt_text', $q)->groupEnd();
        }
        if (in_array($folder, MediaLibrary::FOLDERS, true)) {
            $model->where('folder', $folder);
        }

        return [$model->orderBy('id', 'DESC')->paginate($perPage, 'admin'), $model->pager, $q, $folder];
    }
}
