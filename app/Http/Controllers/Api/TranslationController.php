<?php
/**
 * Created by PhpStorm.
 * User: romeo
 * Date: 17/10/24
 * Time: 上午6:49
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\Task;
use App\Http\Controllers\Service\Timeline;
use App\Http\Controllers\Service\Tool;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    const RETRIVING = -1;
    const READY = 0;
    const TRANSLATING = 1;
    const TRANSLATED = 2;
    const REVIEWING = 3;
    const MERGED = 4;
    const POSTED = 5;

    /**
     * 获取制定状态的文章
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function index()
    {
        if ($this->request->input("status") === "awaiting") {
            $status = array(
                self::READY,
                self::TRANSLATED
            );
        } else if ($this->request->input("status") === "progressing") {
            $status = array(
                self::TRANSLATING,
                self::REVIEWING
            );
        } else {
            $status = array(
                self::POSTED
            );
        }

        try {
            $translations = DB::table("translation")
                ->join("recommend", "recommend.id", "=", "translation.recommend")
                ->join("category", "recommend.category", "=", "category.id")
                ->select("translation.id", "translation.translator", "translation.cdate", "recommend.title", "recommend.description", "recommend.recommender", "category.category")
                ->whereIn("translation.status", $status)
                ->orderBy("translation.udate", "ASC")
                ->skip($this->start)
                ->take($this->offset)
                ->get();

            $set = array();

            foreach ($translations as $v) {
                $set[] = $v->recommender;
                $set[] = $v->translator;
            }

            $users = DB::table("user")
                ->select("name", "avatar", "id")
                ->whereIn("id", array_unique($set))
                ->get();

            $uSet = array(0 => "");

            foreach ($users as $u) {
                $uSet[$u->id] = $u;
            }

            foreach ($translations as $k => $v) {
                $translations[$k]->translator = $uSet[intval($v->translator)];
                $translations[$k]->recommender = $uSet[intval($v->recommender)];
            }
            return response()->json($translations, 200);
        } catch (\Throwable $e) {
            return response("Service Not Available", 503);
        }
    }

    /**
     * 添加新文章（抓取中）
     * @param Timeline $timeline
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function store(Timeline $timeline)
    {
        if ($mandatory = $this->isNull(["rid", "file", "tduration", "rduration", "tscore", "rscore", "word"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        $tid = DB::table("translation")->insertGetId([
            "file" => $this->request->input("file"),
            "recommend" => $this->request->input("rid"),
            "translatePoint" => $this->request->input("tscore"),
            "reviewPoint" => $this->request->input("rscore"),
            "translateDuration" => $this->request->input("tduration"),
            "reviewDuration" => $this->request->input("rduration"),
            "word" => $this->request->input("word"),
            "status" => self::RETRIVING,
            "udate" => date("Y-m-d H:i:s"),
            "cdate" => date("Y-m-d H:i:s")
        ]);

        if ($tid == false) {
            return response("Not Implemented", 501);
        }

        $timeline->write($tid,
            DB::table("recommend")
                ->where("id", $this->request->input("rid"))
                ->value("recommender")
            , "推荐成功");
        return response("OK", 200);
    }

    /**
     * 获取指定文章详情
     * @param $id
     * @param Timeline $timeline
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Timeline $timeline)
    {
        $translation = DB::table("translation")
            ->join("recommend", "recommend.id", "=", "translation.recommend")
            ->join("category", "category.id", "=", "recommend.category")
            ->select("translation.*", "recommend.title", "recommend.description", "recommend.recommender", "recommend.url", "category.category")
            ->where("id", $id)
            ->first();

        $translation->timeline = $timeline->read($id);

        if (boolval($translation->translator)) {
            $translation->translator = DB::table("user")
                ->select("id", "name", "avatar")
                ->where("id", $translation->translator)
                ->first();
            $translation->recommender = "";
        } else {
            $translation->recommender = DB::table("user")
                ->select("id", "name", "avatar")
                ->where("id", $translation->recommender)
                ->first();
        }

        return response()->json($translation);
    }

    /**
     * 更新文章信息（管理员）
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function update($id)
    {
        if ($mandatory = $this->isNull(["tscore", "rscore", "tduration", "rduration", "word", " title"])) {
            return response()->json(["message" => $mandatory . " 为空！"], 400);
        }

        if (
        DB::table("translation")->where("id", $id)->update([
            "translatePoint" => $this->request->input("tscore"),
            "reviewPoint" => $this->request->input("rscore"),
            "translateDuration" => $this->request->input("tduration"),
            "reviewDuration" => $this->request->input("rduration"),
            "word" => $this->request->input("word"),
            "title" => $this->request->input("title"),
            "poster" => $this->request->input("poster"),
            "description" => $this->request->input("originDesc")
        ])
        ) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 处理 PR
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function handlePR()
    {
        $pr = $this->request->input("pull_request");
        $action = $this->request->input("action");
        $admin = array_filter(explode(",", env("GITHUB_ADMIN")));
        $user = $pr["user"]["login"];

        // 处理管理员 PR 添加新文章的 webhook 请求
        if (in_array($user, $admin) && $action == "closed" && $pr["merged"] == true) {
            return $this->setReady();
        }

        // 处理普通用户 PR 的 webhook 请求
        // PR 被创建（翻译完成）
        if ($action == "opened") {
            return $this->setTranslated();
        }

        // PR 被 merge（校对完成）
        if ($action == "closed" && $pr["merged"] == true) {
            return $this->setMerged();
        }
    }

    /**
     * 设置文章为待认领翻译状态
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setReady()
    {
        if ($this->updateOnPr(["status" => self::READY])) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 更新 PR 中的文章
     * @param array $data
     * @return mixed
     */
    public function updateOnPr(array $data)
    {
        $file = $this->fileOnPr();
        return DB::table("translation")->where("file", $file)->update($data);
    }

    /**
     * 获取 PR 中的文章
     * @return mixed
     */
    public function fileOnPr()
    {
        $pr = $this->request->input("pull_request");
        preg_match(
            "/^diff --git a\/(.*?) b\/(.*?)\n/",
            Tool::sendRequest($pr["diff_url"], 'GET'),
            $file
        );

        return $file[1];
    }

    /**
     * 设置文章状态为等待校对
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setTranslated()
    {
        if (
        $this->updateOnPr([
            "pr" => $this->request->input("pull_request")["id"],
            "status" => self::TRANSLATED
        ])
        ) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 设置文章为待分享状态
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setMerged()
    {
        if (
        $this->updateOnPr([
            "title" => $this->request->input("pull_request")["title"],
            "status" => self::MERGED
        ])
        ) {
            return response("OK", 200);
        } else {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 认领翻译
     * @param Task $task
     * @param Timeline $timeline
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function claimTranslate(Task $task, Timeline $timeline)
    {
        $request = $this->request;

        if ($this->checkMission($request->input("uid"), "translating")) {
            return response()->json("您还有未完成的翻译任务！", 403);
        }

        try {
            DB::transaction(function () use ($request, $task, $timeline) {
                DB::table("translation")
                    ->where("id", $request->input("id"))
                    ->update([
                        "status" => self::TRANSLATING,
                        "translator" => $request->input("uid")
                    ]);
                $timeline->write($request->input("id"), $request->input("uid"), "认领翻译");
                $task->store($request->input("uid"), $request->input("id"), "translating");
            });
            return response("OK", 200);
        } catch (\Throwable $e) {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 检查用户是否有正在进行的任务
     * @param int $user
     * @param string $mission
     * @return mixed
     */
    public function checkMission(int $user, string $mission)
    {
        return DB::table("userDetail")->where("user", $user)->value($mission);
    }

    /**
     * 认领校对
     * @param Timeline $timeline
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function claimReview(Timeline $timeline, Task $task)
    {
        $request = $this->request;

        if ($this->checkMission($request->input("uid"), "reviewing")) {
            return response()->json("您还有未完成的校对任务！", 403);
        }

        try {
            DB::transaction(function () use ($request, $timeline, $task) {
                $timeline->write($request->input("id"), $request->input("uid"), "认领校对");
                $task->store($request->input("uid"), $request->input("id"), "reviewing");
                if ($task->count($request->input("id"), "reviewing") == 2) {
                    DB::table("translation")->where("id", $request->input("id"))->update(["status" => self::REVIEWING]);
                }
            });
            return response("OK", 200);
        } catch (\Throwable $e) {
            return response("Not Implemented", 501);
        }
    }

    /**
     * 分享文章
     * @param $id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function post($id, Task $task, Timeline $timeline)
    {
        $request = $this->request;

        $translation = DB::table("translation")
            ->select("translatePoint", "reviewPoint")
            ->where([["id", "=", $id], ["status", "=", self::MERGED]])
            ->first();

        if ($translation == null) {
            return response("Bad Request", 400);
        }

        try {
            DB::transaction(function () use ($request, $id, $translation, $task, $timeline) {
                DB::table("translation")->where("id", $id)->update([
                    "link" => $this->request->input("link"),
                    "status" => self::POSTED
                ]);
                DB::table("userDetail")->where("translating", $id)->increment("point", $translation->translatePoint);
                DB::table("userDetail")->where("reviewing", $id)->increment("point", $translation->reviewPoint);
                $timeline->write($id, $request->input("uid"), "分享文章");
                $task->flush($id, "translating");
                $task->flush($id, "reviewing");
            });
            return response("OK", 200);
        } catch (\Throwable $e) {
            return response("Not Implemented", 501);
        }
    }
}