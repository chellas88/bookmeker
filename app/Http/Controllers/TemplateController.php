<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Templates;

class TemplateController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
      $templates = Templates::all();
      return view('templates', ['data' => $templates]);
  }

  public function getAllTemplates()
  {
      $templates = Templates::all();
      return $templates;
  }

  public function createTemplate(Request $request)
  {
      $id = Templates::insertGetId([
          'name' => $request['name'],
          'is_url' => $request['is_url'],
          'message' => $request['text_message'],
          'url' => $request['url'],
          'created_at' => now(),
          'updated_at' => now()
      ]);
      return 'success';
  }
  public function getTemplateById(Request $request)
  {
      $template = Templates::where('id', $request['id'])->first();
      return $template;
  }

  public function updateTemplate(Request $request)
  {
      $template = Templates::where('id', $request['id'])->first();

      $template->name = $request['name'];
      $template->is_url = $request['is_url'];
      $template->message = $request['text_message'];
      $template->url = $request['url'];
      $template->updated_at = now();

      return $template->save();
  }

  public function deleteTemplate(Request $request)
  {
    return Templates::destroy($request['id']);
  }
}
