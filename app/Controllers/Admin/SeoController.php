<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\SeoPage;
use App\Services\SeoService;

class SeoController extends Controller
{
    public function index(Request $request): Response
    {
        $pages = SeoPage::getAll();
        return $this->view('admin.seo', ['pages' => $pages]);
    }

    public function store(Request $request): Response
    {
        $data = [
            'category' => sanitize($request->input('category', '')),
            'value' => sanitize($request->input('value', '')),
            'h1' => sanitize($request->input('h1', '')),
            'meta_title' => sanitize($request->input('meta_title', '')),
            'meta_description' => sanitize($request->input('meta_description', '')),
            'seo_text_template' => $request->input('seo_text_template', ''),
        ];

        $data['url_path'] = '/servers/' . $data['category'] . '/' . slug($data['value']);

        $errors = $this->validate($data, [
            'category' => 'required',
            'value' => 'required',
            'h1' => 'required',
            'meta_title' => 'required|max:160',
            'meta_description' => 'required|max:320',
        ]);

        if ($errors) {
            flash('error', implode('. ', $errors));
            return $this->redirect('/admin/seo');
        }

        SeoPage::create($data);
        flash('success', 'SEO page created.');
        return $this->redirect('/admin/seo');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $page = SeoPage::find($id);
        if (!$page) {
            flash('error', 'Page not found.');
            return $this->redirect('/admin/seo');
        }

        SeoPage::update($id, [
            'h1' => sanitize($request->input('h1', '')),
            'meta_title' => sanitize($request->input('meta_title', '')),
            'meta_description' => sanitize($request->input('meta_description', '')),
            'seo_text_template' => $request->input('seo_text_template', ''),
        ]);

        flash('success', 'SEO page updated.');
        return $this->redirect('/admin/seo');
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->param('id');
        SeoPage::delete($id);
        flash('success', 'SEO page deleted.');
        return $this->redirect('/admin/seo');
    }

    public function recalculate(Request $request): Response
    {
        $count = SeoService::recalculate();
        flash('success', "Recalculated {$count} SEO pages.");
        return $this->redirect('/admin/seo');
    }
}
