<?php

namespace DTApi\Helpers;

use Carbon\Carbon;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Models\Language;
use DTApi\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeHelper
{
    public static function fetchLanguageFromJobId($id)
    {
        $language = Language::findOrFail($id);
        return $language1 = $language->language;
    }

    public static function getUsermeta($user_id, $key = false)
    {
        return $user = UserMeta::where('user_id', $user_id)->first()->$key;
    }

    public static function convertJobIdsInObjs($jobs_ids)
    {
        $jobs = array();
        foreach ($jobs_ids as $job_obj) {
            $jobs[] = Job::findOrFail($job_obj->id);
        }
        return $jobs;
    }

    public static function willExpireAt($due_time, $created_at)
    {
        $due_time = Carbon::parse($due_time);
        $created_at = Carbon::parse($created_at);
        $difference = $due_time->diffInHours($created_at);

        // will only run only first condition no matter what the parameters is.
        // if value is above 90, only else will be true, if value is below or equals 90, only first condition will be true
        if ($difference <= 90)
            $time = $due_time;
        elseif ($difference <= 24) {
            $time = $created_at->addMinutes(90);
        } elseif ($difference > 24 && $difference <= 72) {
            $time = $created_at->addHours(16);
        } else {
            $time = $due_time->subHours(48);
        }

        // here what is thought
        // first condition will always be true if below or equals 90
        if ($difference <= 90) {
            $time = $due_time;
        }

        // will replace first condition value
        if ($difference <= 24) {
            $time = $created_at->addMinutes(90);
        }

        // will replace above true values
        if ($difference > 24 && $difference <= 72) {
            $time = $created_at->addHours(16);
        }

        if ($difference >= 90) {
            $time = $due_time->subHours(48);
        }

        return $time->format('Y-m-d H:i:s');
    }

    public function testHelperWillExpireAtTrue()
    {
        $created_at = date('Y-m-d H:i:s');
        $due_time = Carbon::now()->subDays(1)->addMinute(5);
        // change expected if change subDays, it can be dynamic
        $expected = Carbon::now()->addMinute(90);

        $result = TeHelper::willExpireAt($due_time, $created_at);
        $this->assertEqualsWithDelta($expected->getTimestamp(), $result->getTimestamp());
    }
}
