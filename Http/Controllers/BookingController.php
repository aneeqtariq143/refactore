<?php

namespace DTApi\Http\Controllers;

use DB;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Monolog\Logger;

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
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            try {
                $response = [
                    'success' => true,
                    'message' => 'ok',
                    'data' => $this->repository->getUsersJobs($user_id),
                    'error_code' => null,
                ];
            }catch (\Exception $exception){
                $response = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'data' => [],
                    'error_code' => 404,
                ];
            }

        } elseif ($request->__authenticatedUser->user_type == config('access.admin_role_id') || $request->__authenticatedUser->user_type == config('superadmin_role_id')) {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->getAll($request),
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->with('translatorJobRel.user')->find($id),
                'error_code' => null,
            ];
        }catch (\Exception $exception){
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => '',
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreBookingRequest $request)
    {
        /*
         * We need to perform data validation before sending data to repository
         */
        $data = $request->validated();

        $cuser = $request->__authenticatedUser;
        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->store($cuser, $data),
                'error_code' => null,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update($id, UpdateBookingRequest $request)
    {
        /*
         * We need to perform data validation before sending data to repository
         */
        $data = $request->validated();

        $cuser = $request->__authenticatedUser;
        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser),
                'error_code' => null,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function immediateJobEmail(Request $request)
    {
        /*
         * We need to perform data validation before sending data to repository
         */
        $data = $request->validated();

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->storeJobEmail($data),
                'error_code' => null,
            ];
        }catch (ModelNotFoundException $exception){
            $response = [
                'success' => false,
                'message' => 'Not Found',
                'data' => [],
                'error_code' => 404,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return null|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            try {
                $response = [
                    'success' => true,
                    'message' => 'ok',
                    'data' => $this->repository->getUsersJobsHistory($user_id, $request),
                    'error_code' => null,
                ];

            }catch (\Exception $exception){
                report($exception);
                $response = [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'data' => [],
                    'error_code' => null,
                ];
            }
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->acceptJob($data, $user),
                'error_code' => null,
            ];

        }catch (ModelNotFoundException $exception){
            $response = [
                'success' => false,
                'message' => 'Not Found',
                'data' => [],
                'error_code' => 404,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function acceptJobWithId(Request $request)
    {
        $job_id = $request->get('job_id');
        $user = $request->__authenticatedUser;

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->acceptJobWithId($job_id, $user),
                'error_code' => null,
            ];

        }catch (ModelNotFoundException $exception){
            $response = [
                'success' => false,
                'message' => 'Not Found',
                'data' => [],
                'error_code' => 404,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->cancelJob($data, $user),
                'error_code' => null,
            ];

        }catch (ModelNotFoundException $exception){
            $response = [
                'success' => false,
                'message' => 'Not Found',
                'data' => [],
                'error_code' => 404,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->endJob($data),
                'error_code' => null,
            ];

        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->customerNotCall($data),
                'error_code' => null,
            ];

        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPotentialJobs(Request $request)
    {
        $user = $request->__authenticatedUser;

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->getPotentialJobs($user),
                'error_code' => null,
            ];

        }catch (ModelNotFoundException $exception){
            $response = [
                'success' => false,
                'message' => 'Not Found',
                'data' => [],
                'error_code' => 404,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->validate([
            'distance' => 'nullable',
            'time' => 'nullable',
            'jobid' => 'required',
            'session_time' => 'nullable',
            'flagged' => 'required',
            'admincomment' => 'nullable',
            'manually_handled' => 'required',
            'by_admin' => 'required',
            'admincomment' => 'nullable',
        ]);

        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->distanceFeed($data),
                'error_code' => null,
            ];

        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function reopen(Request $request)
    {
        try {
            $response = [
                'success' => true,
                'message' => 'ok',
                'data' => $this->repository->reopen($request),
                'error_code' => null,
            ];

        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendNotifications(Request $request)
    {
        $data = $request->all();

        try {
            $job = $this->repository->find($data['jobid']);
            $job_data = $this->repository->jobToData($job);
            $this->repository->sendNotificationTranslator($job, $job_data, '*');

            $response = [
                'success' => true,
                'message' => 'Push sent',
                'data' => $job_data,
                'error_code' => null,
            ];
        }catch (\Exception $exception){
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        try {
            $job = $this->repository->find($data['jobid']);
            $job_data = $this->repository->jobToData($job);
            $this->repository->sendSMSNotificationToTranslator($job);

            $response = [
                'success' => true,
                'message' => 'SMS sent',
                'data' => $job_data,
                'error_code' => null,
            ];
        } catch (\Exception $e) {
            report($exception);
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => [],
                'error_code' => null,
            ];
        }

        return response($response);
    }

}
