<?php

class jiraController
{
    private $jira = 'https://jira.mcemcw.com/rest/api/latest/';
    private $qaueryIssuesForDeploy = 'search' .
    '?jql=assignee=inor%20AND%20status%20=%20%22Wait%20for%20test%20[PRELIVE]%' .
    '22or%20assignee=inor%20AND%20status%20=%20%22TESTING%20[PRELIVE]%22' .
    'or%20assignee=zsolonukhyna%20AND%20status%20=%20%22Wait%20for%20test%20[PRELIVE]%22' .
    'or%20assignee=zsolonukhyna%20AND%20status%20=%20%22TESTING%20[PRELIVE]%22' .
    'or%20assignee=alyutenko%20AND%20status%20=%20%22Wait%20for%20test%20[PRELIVE]%22' .
    'or%20assignee=alyutenko%20AND%20status%20=%20%22TESTING%20[PRELIVE]%22';

    private function getJiraCreditionals()
    {
        $credentials = base64_decode('aWJhY2hhOks3NTl3bnV1');
        return $credentials;
    }

    public function parseData()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->getJiraCreditionals());
        curl_setopt($ch, CURLOPT_URL, $this->jira . $this->qaueryIssuesForDeploy);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            echo "\n<br />";
            $json = '';
        } else {
            curl_close($ch);
        }
        $issues_array = json_decode($json, true);
        $counter = 0;
        $parsed_data = array();
        foreach ($issues_array["issues"] as $val) {
            $in_array = false;
            $dev = $this->findLastDevInProgress($val["key"]);
            $allComments = $this->getComments($val["key"]);
            $lastComment = $this->findLastComment($dev, $allComments);
            $dateComment = $lastComment[1];
            if (!preg_match('/(.*)wp\-test(.*)/is', $lastComment[0])) continue;
            $prileve = 'http://' . $this->parseComment($lastComment[0]) . '.prelive01.mcemcw.com';
            foreach (array_reverse($parsed_data) as $array_data) {
                if ($array_data["prelive_number"] == $prileve)  $in_array = true;
            }
            if ($in_array) continue;
            $projectName = trim(mb_strtolower($val["fields"]["customfield_11001"]));
            $parsed_data[$counter] = array(
                'prelive_number' => $prileve,
                'domain' => $projectName,
                'developer' => $dev,
                'date' => $dateComment
            );
            $counter++;
        }
        echo json_encode($parsed_data);
    }

    /**
     * get all comments from task in Jira
     * @param $task
     * @return mixed
     */

    private function getComments($task)
    {
        $commentsURL = $this->jira . 'issue/' . $task . '/comment';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->getJiraCreditionals());
        curl_setopt($ch, CURLOPT_URL, $commentsURL);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            echo "\n<br />";
            $json = '';
        } else {
            curl_close($ch);
        }
        $commentsArray = json_decode($json, true);
        return $commentsArray["comments"];
    }

    /**
     * finding last commentary of developer
     * @param $developer
     * @param $comments
     * @return mixed
     */

    private function findLastComment($developer, $comments)
    {
        foreach (array_reverse($comments) as $comment) {

            if ($comment["author"]["key"] == $developer) {
                if (preg_match('/with(.*)db(.*)/is', $comment["body"]) == 0) {
                    continue;
                }
                return array($comment["body"] , $comment["updated"]);
            }
        }
        return false;
    }

    /**
     * @param $comment - comment from developer
     * @return string - number of wp-test
     */
    private function parseComment($comment)
    {
        foreach (explode(PHP_EOL, $comment) as $comment_string) {
            if (preg_match('/(.*)wp\-test\d{0,3}/is', $comment_string)) {
                return trim($comment_string);
            }
        }
        return '';
    }

    /**
     * @param $task
     * @return string
     * find in issue last developer who changed status to "In progress"
     */

    private function findLastDevInProgress($task)
    {
        $commentsURL = $this->jira . 'issue/' . $task . '?expand=changelog';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->getJiraCreditionals());
        curl_setopt($ch, CURLOPT_URL, $commentsURL);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            echo "\n<br />";
            $json = '';
        } else {
            curl_close($ch);
        }
        $history = json_decode($json, true);
        foreach (array_reverse($history["changelog"]["histories"]) as $val) {
            $count = 0;
            foreach ($val["items"] as $item) {
                if ($item["to"] == "3") {
                    $developer = $val["author"]["name"];
                    return $developer;
                }
                $count++;
            }
        }
    }
}