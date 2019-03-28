<?php

namespace App;

use Medoo\Medoo;

/**
 * Class MainSrc
 * @property Medoo $db
 */
class Service
{
    private $cfg;
    private $db;
    private $user;
    private $fileOpenResult = __DIR__ . '/../conf/result.txt';

    public static $stat = [
        1 => ['name' => 'КС', 'key' => 1, 'data' => 0],
        2 => ['name' => 'ПКС', 'key' => 2, 'data' => 0],
        3 => ['name' => 'С', 'key' => 4, 'data' => 0],
        4 => ['name' => 'ТМ', 'key' => 8, 'data' => 0],
        5 => ['name' => 'ТМП', 'key' => 16, 'data' => 0],
    ];


    public function __construct()
    {
        $this->cfg = parse_ini_file(__DIR__ . '/../conf/db.ini');
        $this->db = new Medoo([
            'database_type' => $this->cfg['database_type'],
            'database_name' => $this->cfg['database_name'],
            'server' => $this->cfg['server'],
            'username' => $this->cfg['username'],
            'password' => $this->cfg['password'],
        ]);
    }

    private function getUserSid()
    {
        return session_id();
    }

    public function getUser($force = false)
    {
        if ($force || is_null($this->user)) {

            $user = $this->db->get('users', ['id', 'sid', 'name', 'done'],
                [
                    'sid' => $this->getUserSid()
                ]);
            $this->user = $user ? $user : null;
        }
        return $this->user;
    }

    public function getUserId()
    {
        $user = $this->getUser();

        return $user ? $user['id'] : null;
    }

    public function storeUserData($data)
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $data['ip'] = $ip;
        $user = $this->getUser();
        if ($user) {
            $this->db->update('users',
                $data,
                ['id' => $user['id']]
            );
        } else {
            $data['sid'] = $this->getUserSid();
            $this->db->insert('users', $data);
        }
        $this->getUser(true);
    }

    public function getQuestions()
    {
        $rows = $this->db->select(
            'questions',
            ['id', 'name'],
            ['ORDER' => 'order']
        );

        $result = [];
        foreach ($rows as $row) {
            $row['answers'] = $this->getAnswers($row['id']);
            $result[] = $row;
        }
        return $result;
    }

    public function getAnswers($question_id)
    {
        return $this->db->select(
            'answers',
            ['id', 'name'],
            [
                'question_id' => $question_id,
                'ORDER' => 'order',
            ]
        );
    }

    public function storeUserResult($question_id, $answer_id)
    {
        // remove before
        if (!($question_id > 0 && $answer_id > 0)) {
            return false;
        }
        $this->db->delete('user_answers',
            [
                'question_id' => $question_id,
                'answer_id' => $answer_id,
                'user_id' => $this->getUserId()
            ]
        );
        // store new value
        $data = [
            'user_id' => $this->getUserId(),
            'question_id' => $question_id,
            'answer_id' => $answer_id,
        ];

        $row = $this->db->get('user_answers', ['id'], [
            'user_id' => $this->getUserId(),
            'question_id' => $question_id,
        ]);
        $data['answer_id'] = $answer_id;
        if ($row) {
            $this->db->update('user_answers', $data, ['id' => $row['id']]);
        } else {
            $this->db->insert('user_answers', $data);
        }

        if ($this->isLastQuestion($question_id)) {
            $this->db->update('users',
                ['done' => 1],
                ['sid' => $this->getUserSid()]
            );
        }
        return true;
    }

    public function getUserResult()
    {
        if (!$this->isResultOpen()) {
            return false;
        }
        $user = $this->getUser();
        $userAnswers = $this->db->select('user_answers',
            [
                '[><]answers' => ['answer_id' => 'id']
            ],
            [
                'user_answers.user_id',
                'answers.price',
            ],
            ['user_id' => $user['id']]
        );
        $result = self::$stat;
        foreach ($userAnswers as $userAnswer) {
            foreach ($result as $key => $row) {
                if (($userAnswer['price'] & $row['key']) > 0) {
                    $result[$key]['data']++;
                }
            }
        }
        $sort = [];
        $names = [];
        $result = array_values($result);
        foreach ($result as $row) {
            $sort[] = $row['data'];
            $names[] = $row['name'];
        }
        // sort
        array_multisort($sort, SORT_NUMERIC, SORT_DESC, $names, SORT_STRING, SORT_ASC, $result);
        return $result;
    }

    public function getStatistic()
    {
        $userAnswers = $this->db->select('user_answers',
            [
                '[><]answers' => ['answer_id' => 'id']
            ],
            [
                'user_answers.user_id',
                'answers.price',
            ]
        );
        $result = self::$stat;
        foreach ($userAnswers as $userAnswer) {
            foreach ($result as $key=>$row) {
                if (($userAnswer['price'] & $row['key']) > 0) {
                    $result[$key]['data']++;
                }
            }
        }
        $csv = "null,Итого\n";
        foreach ($result as $row) {
            $csv .= $row['name'] . ',' . $row['data'] . "\n";
        }
        return $csv;
    }

    public function test()
    {
        $this->getStatistic();
        /**
         * $q = $this->db->select('questions', '*', ['ORDER' => 'order']);
         * foreach ($q as $row) {
         * $a = $this->db->select('answers', '*', ['question_id' => $row['id'], 'ORDER' => 'order']);
         * echo $row['name'] . ":\n";
         * foreach ($a as $aRow) {
         * echo "- " . $aRow['name'] . ":\n";
         * foreach (self::$stat as $item) {
         * if (($aRow['price'] & $item['key']) > 0) {
         * echo '--' . $item['name'] . "\n";
         * }
         * }
         * }
         * }**/
    }

    public function isLastQuestion($question_id)
    {
        $q = $this->db->get('questions', ['id'], ['ORDER' => ['order' => 'DESC']]);
        return ($q && $q['id'] == $question_id);
    }

    public function resetUserDone()
    {
        $this->db->update('users',
            ['done' => 0],
            ['sid' => $this->getUserSid()]
        );
        $user = $this->getUser();
        if ($user['id'] > 0) {
            $this->db->delete('user_results', ['user_id' => $user['id']]);
        }
    }

    private function isResultOpen()
    {
        $isOpen = false;
        if (file_exists($this->fileOpenResult)) {
            $r = file_get_contents($this->fileOpenResult);
            $isOpen = ($r == 1);
        }
        return $isOpen;
    }

    public function switchResultOpen($result)
    {
        $store = ($result == 1 ? 1 : 0);
        file_put_contents($this->fileOpenResult, $store);
        return $store;
    }
}

