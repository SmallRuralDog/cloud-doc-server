<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/30
 * Time: 10:35
 */

namespace App\Http\Controllers;


use App\Models\Doc;
use Illuminate\Http\Request;
use Validator;

class DocCollectController extends Controller
{
    public function collect_doc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'desc' => 'required',
            'doc_class_id' => 'required',
            'cover' => 'required',
            'h_cover' => 'required',
            'source' => 'required',
            'source_url' => 'required',
        ]);
        if ($validator->fails()) {
            return 0;
        }

        $title = $request->input("title");
        $doc_class_id = $request->input("doc_class_id");
        $desc = $request->input("desc");
        $cover = $request->input("cover");
        $h_cover = $request->input("h_cover");
        $source = $request->input("source");
        $source_url = $request->input("source_url");

        $doc = Doc::query()->firstOrCreate(['source_url' => $source_url], [
            'title' => $title,
            'doc_class_id' => $doc_class_id,
            'desc' => $desc,
            'cover' => $cover,
            'h_cover' => $h_cover,
            'source' => $source,
            'source_url' => $source_url,
            'user_id'=>0
        ]);

        return $doc->id;

    }

}