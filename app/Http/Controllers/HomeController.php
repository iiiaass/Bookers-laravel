<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = \Auth::user();    //ログインしているユーザーの情報をViewに渡す
        //メモ一覧を取得する
        $memos = Memo::where('user_id', $user['id'])->where('status',1)->orderby('updated_at','DESC')->get();   //自分が所有しているメモ,かつstatusが１のメモを取得する    //where->取ってくるデータの条件を指定できる
        return view('home',compact('user','memos'));                                                     //orderby->並べ方を指定できる(ASC=昇順、DESC=降順)
    }

    public function create()
    {
        $user = \Auth::user();    //ログインしているユーザーの情報をViewに渡す
        $memos = Memo::where('user_id', $user['id'])->where('status',1)->orderby('updated_at','DESC')->get();   //自分が所有しているメモ,かつstatusが１のメモを取得する    //where->取ってくるデータの条件を指定できる
        return view('create',compact('user','memos'));
    }

    public function store(Request $request) //Requestを使うと、フォームに入力されたメモの内容、ユーザーidをコントローラで受け取ることが出来る。//
    {
        $data = $request->all();  //$dataの中に$request allでHTMLから投げられたデータを全て$dataに入れている
        //dd($data);    //dd->その$dataの中身を分解して画面で確認できる
        // POSTされたデータをDB（memosテーブル）に挿入
        // MEMOモデルにDBへ保存する命令を出す
        
        //同じタグがあるか確認
        $exist_tag = Tag::where('name', $data['tag'])->where('user_id', $data['user_id'])->first();     //nameが$dataのtag名が存在するかつ、user_idが露軍しているuser    ->first
        if( empty($exist_tag['id']) ){     //empty->exist_tagのidがもし空ならば（同じタグが存在しないのであれば））
        //    //先にタグをインサート（リレーション）
        $tag_id = Tag::insertGetId(['name' => $data['tag'], 'user_id' => $data['user_id']]);   //tagの名前には入力したタグネームを入れる、user_idにはPOSTされてくるuser_idを入れる
      }else{
        $tag_id = $exist_tag['id'];
      }
   
        //タグのidが判明する
        //他でidをmemosテーブルに入れてあげる
        $memo_id = Memo::insertGetId([   //insert それぞれデータを定義して、データベースに挿入していっている
            'content' => $data['content'],
             'user_id' => $data['user_id'], 
             'tag_id' => $tag_id,
             'status' => 1
        ]); 
        
        // リダイレクト処理->別のページへ遷移すること
        return redirect()->route('home');
    }
    

    public function edit($id){    //$idは引数　editのroutingと対応している
        // 該当するIDのメモをデータベースから取得
        $user = \Auth::user();
        $memo = Memo::where('status', 1)->where('id', $id)->where('user_id', $user['id'])  //statusが1かつ、memosテーブルのidがURLパラメータ（URLの数字）と同じものかつ、userのidが今ログインしているuserのidと一致すること
          ->first();    //first->条件が一致した物を一行だけ取ってくるメソッド
        //   dd($memo);
        //取得したメモをViewに渡す
        $memos = Memo::where('user_id', $user['id'])->where('status',1)->orderby
        ('updated_at','DESC')->get();   //自分が所有しているメモ,かつstatusが１のメモを取得する    //where->取ってくるデータの条件を指定できる
        $tags = Tag::where('user_id',$user['id'])->get();    //getにすることで該当するものを全て取ってきてくれる
        return view('edit',compact('memo','user','memos','tags'));
    }

    public function update(Request $request, $id)    //$id->editのURLパラメータと同じく$idと書くことによってどこの行を更新するか受け取ることができる
    {
        $inputs = $request->all();
        // dd($inputs);
        Memo::where('id', $id)->update(['content' => $inputs['content'],'tag_id'=>$inputs['tag_id']]);     //whereでどこをupdateするのか指定する。idはURLパラメータに入っているもの、そのあとupdateしたい内容を配列で指定。contentの内容を更新したいので$inputsのcontentの内容に更新します。という意味
        return redirect()->route('home');    // リダイレクト処理->別のページへ遷移すること                                                                        
    }


    public function update(Request $request, $id)    //$id->editのURLパラメータと同じく$idと書くことによってどこの行を更新するか受け取ることができる
    {
        $inputs = $request->all();
        // dd($inputs);
        Memo::where('id', $id)->update(['content' => $inputs['content']]);     //whereでどこをupdateするのか指定する。idはURLパラメータに入っているもの、そのあとupdateしたい内容を配列で指定。contentの内容を更新したいので$inputsのcontentの内容に更新します。という意味
        return redirect()->route('home');    // リダイレクト処理->別のページへ遷移すること                                                                        
    }
}   


