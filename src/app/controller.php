<?php

namespace App;

class Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new Service();
    }

    public function getUser()
    {
        return $this->sendSuccess($this->service->getUser());
    }

    public function getQuestions()
    {
        return $this->sendSuccess($this->service->getQuestions());
    }

    public function getUserResult()
    {
        return $this->sendSuccess($this->service->getUserResult());
    }

    public function getStatistic()
    {
        return $this->service->getStatistic();
    }

    public function saveUserData($data)
    {
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = trim($data['name']);
        }
        if (isset($data['mail'])) {
            $updateData['mail'] = trim($data['mail']);
        }
        if (isset($data['edu'])) {
            $updateData['edu'] = trim($data['edu']);
        }
        if ($updateData) {
            $this->service->storeUserData($updateData);
            return $this->sendSuccess();
        } else {
            return $this->sendError('nothing_to_change');
        }
    }

    public function saveUserAnswer($data)
    {
        if (!($data['question_id'] > 0 && $data['answer_id'] > 0)) {
            return $this->sendError('no_answer');
        }
        if ($this->service->storeUserResult($data['question_id'], $data['answer_id'])) {
            return $this->sendSuccess();
        }
        return $this->sendError('something_wrong');
    }

    private function sendSuccess($data = null)
    {
        return [
            'status' => true,
            'data' => $data
        ];
    }

    private function sendError($message = '')
    {
        return [
            'status' => false,
            'data' => $message
        ];
    }

    public function test()
    {
        $this->service->sendSocketMessage('answer', []);
    }

    public function restartQuestions()
    {
        $this->service->resetUserDone();
        return $this->sendSuccess();
    }

    public function switch($status)
    {
        return $this->service->switchResultOpen($status);
    }
}