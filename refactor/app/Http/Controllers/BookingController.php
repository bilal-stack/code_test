<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * Display Listing of resource
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->get('user_id')) {
            $response = $this->repository->getUsersJobs($request->get('user_id'));

        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') ||
            $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * Show specified resource
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);
        return response($job);
    }

    /**
     * Store new resource
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $response = $this->repository->store($request->__authenticatedUser, $request->all());
        return response($response);
    }

    /**
     * Update specified resource
     *
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cUser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cUser);
        return response($response);
    }

    /**
     * Send booking confirmation email
     *
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $response = $this->repository->storeJobEmail($request->all());
        return response($response);
    }

    /**
     * Get user job history
     *
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }
        return null;
    }

    /**
     * Accept a new job
     *
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $response = $this->repository->acceptJob($request->all(), $request->__authenticatedUser);
        return response($response);
    }

    /**
     * Accept Job with id
     *
     * @param Request $request
     * @return mixed
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);
        return response($response);
    }

    /**
     * Cancel Job with Ajax
     *
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $response = $this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser);
        return response($response);
    }

    /**
     * End job
     *
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
       $response = $this->repository->endJob($request->all());
       return response($response);
    }

    /**
     * Customer not attending call
     *
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request)
    {
        $response = $this->repository->customerNotCall($request->all());
        return response($response);
    }

    /**
     * Get Potentials jobs
     *
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $response = $this->repository->getPotentialJobs($request->__authenticatedUser);
        return response($response);
    }

    /**
     * Update Distance Feed resource
     *
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();
        $distance = "";
        $time = "";
        $session = "";
        $flagged = 'no';
        $manually_handled = 'no';
        $by_admin = 'no';
        $admincomment = "";

        if (isset($data['distance']) && $data['distance'] != "") {
            $distance = $data['distance'];
        }

        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        }

        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        }

        if ($data['flagged'] == 'true') {
            if ($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        }

        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $admincomment = $data['admincomment'];
        }

        if ($time || $distance) {
            Distance::where('job_id', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged,
                'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }

        return response('Record updated!');
    }

    /**
     * Re-open job
     *
     * @param Request $request
     * @return mixed
     */
    public function reopen(Request $request)
    {
        $response = $this->repository->reopen($request->all());
        return response($response);
    }

    /**
     * Re-send Notifications
     *
     * @param Request $request
     * @return mixed
     */
    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);

        try {
            $count = $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent', 'count' => $count]);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }
}
