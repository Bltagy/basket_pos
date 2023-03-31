<?php

namespace App\Http\Controllers\API\Backend;

use App\Http\Controllers\Controller;
use App\Http\Traits\Response;
use App\Http\Transformer\BackEnd\CourseTransformer;
use App\Http\Transformer\BackEnd\VideoTransformer;
use App\Model\Course;
use App\Model\Tempfile;
use App\Model\Video;
use App\Model\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    use Response;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderBy = $request->has('orderBy') ? $request->orderBy : 'created_at';
        $order = $request->has('order') ? $request->order : 'DESC';
        $userId = $request->has('userId') ? $request->userId : 'DESC';
        $course = $request->has('course') ? $request->course : false;
        $backedUp = $request->has('backedUp') ? $request->backedUp : false;
        $skip = $request->has('skip') ? $request->skip : 0;
        $is_super = ($request->hasHeader('x-admin-type')) ? $request->header('x-admin-type') : 0;
        
        
        $videos = $request->user()->hasRole('super-admin') ? new Video  : Video::whereIn('course_id', $request->user()->accessCourses()->pluck('course_id'));
        if ($request->has('search')) {
            $videos = $videos->orWhereTranslationLike('title', '%' . $request->search . '%')
                ->orWhereTranslationLike('description', '%' . $request->search . '%');
        }
        if ($userId && is_numeric($userId)) {
            $videos = $videos->whereHas('course', function ($query) use ($userId) {
                $query->whereHas('accessUsers', function ($q) use ($userId) {
                    $q->where('user_id', '=', $userId);
                });
            });
        }
        if ($userId && is_numeric($userId)) {
            $videos = $videos->whereHas('course', function ($query) use ($userId) {
                $query->whereHas('accessUsers', function ($q) use ($userId) {
                    $q->where('user_id', '=', $userId);
                });
            });
        }
        if ($course && is_numeric($course)) {
            $videos = $videos->where('course_id', '=', $course);
        }
        if ($backedUp == 1) {
            $videos = $videos->where('backedUp', '=', 1);
        }
        if ($backedUp == 2) {
            $videos = $videos->where('backedUp', '=', 0);
        }
        if (!$is_super){
            $videos = $videos->where('backedUp', '=', 0);
        }
        $orderByActual = $orderBy == 'views' ? 'created_at' : $orderBy;
        $count = $videos->count();
        $videos = $videos->take(10)->skip($skip)->orderBy($orderByActual, $order)->get();

        $videosOb = fractal()
            ->collection($videos)
            ->transformWith(new VideoTransformer(false, $userId))
            ->toArray();
        if ($orderBy == 'views') {
            if ($order == 'asc') {
                $videosOb = collect($videosOb)->sortByDesc('views')->values();
            } else {
                $videosOb = collect($videosOb)->sortBy('views')->values();
            }
        }
        $data = ['data' => $videosOb, 'count' => $count];
        return $this->dataResponse($data, null, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_en' => 'required',
            'name_ar' => 'required',
            'course_id' => 'required',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), $validator->errors(), 422);
        }

        $data = [
            'en' => [
                'title' => $request->name_en,
                'description' => $request->description_en
            ],
            'ar' => [
                'title' => $request->name_ar,
                'description' => $request->description_ar
            ],
        ];
        $data = array_merge($data, $request->only(['course_id', 'free', 'hide']));
        $video = Video::create($data);
        $video->addMediaFromBase64($request->image['data'])->usingFileName($request->image['name'])
            ->toMediaCollection('images');
        $image = $video->getFirstMediaURL();

        if ($request->has('video')) {

            $media = Tempfile::find($request->video);
            if ($media) {
                $mediaItem = $media->getMedia('tempFile')->first();
                $movedMediaItem = $mediaItem->copy($video, 'video');
            }
        }
        if ($request->has('attachments')) {
            foreach ($request->attachments as $attachment) {
                $media = Tempfile::find($attachment);
                if ($media) {
                    $mediaItem = $media->getMedia('tempFile')->first();
                    $movedMediaItem = $mediaItem->copy($video, 'attachment');
                }
            }
        }
        return $this->dataResponse($image, 'Uploaded successfully!', 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_en' => 'required',
            'name_ar' => 'required',
            'description_en' => 'required',
            'description_ar' => 'required',
            //            'total_hours' => 'required|int',
            //            'price' => 'required|int',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), $validator->errors(), 422);
        }
        $video = Video::find($id);
        if (!$video) {
            return $this->errorResponse('Video not found', [], 404);
        }

        $data = [
            'en' => [
                'name' => $request->name_en,
                'description' => $request->description_en
            ],
            'ar' => [
                'name' => $request->name_ar,
                'description' => $request->description_ar
            ],
        ];
        $data = array_merge(
            $data,
            $request->only(['video_id', 'total_hours', 'course_id', 'year', 'price', 'free', 'hide'])
        );

        $video->update($data);
        if ($request->image) {
            $video->clearMediaCollection('images');
            $video->addMediaFromBase64($request->image['data'])->usingFileName($request->image['name'])
                ->toMediaCollection('images');
            $image = $video->getFirstMediaURL('images');
        }
        return $this->dataResponse('Success', null, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Clone(Request $request, $id)
    {
        $video = Video::find($id);
        if (!$video) {
            return $this->errorResponse('Video not found', [], 404);
        }

        $data = [
            'en' => [
                'title' => $video->translate('en')->title . '--cloned',
                'description' => $video->translate('en')->description
            ],
            'ar' => [
                'title' => $video->translate('ar')->title,
                'description' => $video->translate('ar')->description
            ],
        ];
        $data = array_merge(
            $data,
            [
                'total_hours' => $video->total_hours,
                'course_id' => $video->course_id,
                'year' => $video->year,
                'price' => $video->price,
                'free' => $video->free,
                'hide' => $video->hide
            ]
        );

        $newVideo = Video::create($data);

        $mediaItem = $video->getMedia('video')->first();
        $movedMediaItem = $mediaItem->copy($newVideo, 'video');

        $mediaItem = $video->getMedia('images')->first();
        $movedMediaItem = $mediaItem->copy($newVideo, 'images');

        $attachments = $video->getMedia('attachment');
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $attachment->copy($newVideo, 'attachment');
            }
        }


        return $this->dataResponse('Success', null, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resetViews(Request $request, $id)
    {
        $video = Video::find($id);
        $userId = $request->has('userId') ? $request->userId : 'DESC';
        VideoView::where('user_id', $userId)->where(
            'video_id',
            $video->id
        )->delete();
        return $this->dataResponse('Success', null, 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return $this->errorResponse('Video not found', [], 404);
        }
        $video->delete();
        return $this->dataResponse('Success', null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function videoRelations(Request $request)
    {
        $courses = $request->user()->hasRole('super-admin') ?  Course::orderByTranslation('name')->get() :  $request->user()->accessCourses()->orderByTranslation('name')->get();
        $videos = fractal()
            ->collection($courses)
            ->transformWith(new CourseTransformer(true))
            ->toArray();
        return $this->dataResponse($videos, null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function tempUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);
        if ($validator->fails()) {
            return $this->dataResponse(null, $validator, 422);
        }
        $temp = Tempfile::create();
        $temp->addMedia($request->file)->toMediaCollection('tempFile');
        $image = $temp->getFirstMediaURL('tempFile');
        $name = $temp->getFirstMedia('tempFile')->name;
        return $this->dataResponse(
            ['id' => $temp->id, 'url' => $image, 'name' => $name],
            'Uploaded successfully!',
            200
        );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setTags(Request $request, $id)
    {
        $tags = $request->tags;
        $video = Video::find($id);
        if (!$video) {
            return $this->errorResponse('Video not found', [], 404);
        }
        $video = $video->tags()->sync($tags);
        return $this->dataResponse(
            [],
            'Uploaded successfully!',
            200
        );
    }

    /**
     * @param $id
     * @param  Request  $request
     * Details of videos
     */
    public function backupRestore($id, Request $request)
    {
        $video = Video::find($id);
        
        if (!$video->backedUp){
            $video->hide = 1;
            $video->backedUp = 1;
            return response()->json($video->getMedia('video'));
            $mediaItem = $video->getMedia('video')->first();
            $copiedMediaItem = $mediaItem->move($video, 'video', 'dropbox');
        }else{
            
            $video->hide = 1;
            $video->backedUp = 0;
            $mediaItem = $video->getMedia('video')->first();
            $copiedMediaItem = $mediaItem->move($video, 'video');

        }
        $video->save();
        
        
        return response()->json([
            'status' => 'true',
            'type' => $video->backedUp,
        ], 200);
    }
}
