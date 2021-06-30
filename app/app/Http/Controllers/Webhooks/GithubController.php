<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 14.01.2021
 * Time: 10:47
 */

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Queue\RedisQueue;
use Validator;
use Illuminate\Http\Request;


class GithubController extends Controller
{
    private static $storageBasePath = 'webhooks/github';

    public function receive(Request $request)
    {
        $dir = storage_path(self::$storageBasePath);
        if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir) ) {
            return response()->json(compact('dir'));
            return;
        }
        $file = $dir . '/' . date('Y-m-d_H-i-s') . '.json';
        if (file_exists($file)) {
            $file = $dir . '/' . date('Y-m-d_H-i-s') . '.x.json';
        }

        $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        $REMOTE_HOST = $_SERVER['REMOTE_HOST'];
        $method = $request->method();
        $headers = $request->headers->all();
        $input = $request->all();
        $content = $request->getContent();
        $LOCAL_REPO = $this->$this->getLocalRepo();;
        chdir($LOCAL_REPO);

        $queryGitBranch = $this->gitcmd('git branch --show-current');
        $gitBranch = trim($queryGitBranch['output']);

        $queryGitUrl = $this->gitcmd('git config --get remote.origin.url');
        $gitUrl = trim($queryGitUrl['output']);
        // $gitAuthUrl = str_replace('https://', 'https://78eb140f0ef9bd8d1edd2585b0334b87061fc0d12@', $gitUrl);
        $gitAuthUrl = str_replace('https://', 'https://ConeHead07@', $gitUrl);
        $gitPullCmd = 'git pull ' . $gitAuthUrl . ' ' . $gitBranch;

        file_put_contents($file, json_encode(compact(
            'method',
            'REMOTE_ADDR',
            'REMOTE_HOST',
            'headers',
            'input',
            'content'), JSON_PRETTY_PRINT));

        if ($headers['x-github-event'] && $headers['x-github-event'] === 'push') {
            register_shutdown_function(function() use ($LOCAL_REPO, $gitPullCmd) {
                shell_exec("cd {$LOCAL_REPO} && " . $gitPullCmd);
            });
        }

        return response()->json(['status' => 'OK']);
    }

    public function test(Request $request) {
        $re = [];

        $dir = storage_path(self::$storageBasePath);

        // $LOCAL_REPO = dirname(base_path());
        $LOCAL_REPO = $this->getLocalRepo();
        chdir($LOCAL_REPO);


        $queryGitBranch = $this->gitcmd('git branch --show-current');
        $gitBranch = trim($queryGitBranch['output']);

        $queryGitUrl = $this->gitcmd('git config --get remote.origin.url');
        $gitUrl = trim($queryGitUrl['output']);
        $gitAuthUrl = str_replace('https://', 'https://78eb140f0ef9bd8d1edd2585b0334b87061fc0d12@', $gitUrl);
        $gitPullCmd = 'git pull ' . $gitAuthUrl . ' ' . $gitBranch;

        $re['currentRepo'] = $gitUrl;
        $re['currentAuthRepo'] = $gitAuthUrl;
        $re['currentBranch'] = $gitBranch;
        $re['gitPullCmd'] = $gitPullCmd;
        $re['date'] = $this->cmd('date');
        $re['pwd'] = $this->cmd('pwd');
        $re['ls -al'] = $this->cmd('ls -al');
        $re['cd /application/public/app && ls -al'] = $this->shellCmd("cd $LOCAL_REPO && ls -al");
        $re['git branch'] = $this->gitcmd('git branch');
        $re['git config --get remote.origin.url'] = $this->gitcmd('git config --get remote.origin.url');
        $re['git remote show origin'] = $this->gitcmd('git remote show origin');

        return response()->json($re);
    }

    public function pull(Request $request) {
        $re = $this->gitcmd('git pull');

        return response()->json($re);
    }

    public function fetchAll(Request $request) {
        return response()->json($this->gitcmd('git fetch -v --all'));
    }

    public function branch(Request $request) {
        return response()->json($this->gitcmd('git branch'));
    }

    public function date(Request $request) {
        return response()->json($this->shellCmd('date') );
    }

    public function lsAll(Request $request) {
        $LOCAL_REPO = $this->getLocalRepo();
        chdir($LOCAL_REPO);
        return response()->json([
            'pwd' => $this->cmd('pwd'),
            'pwd' => $this->cmd('ls -al'),
        ]);
    }

    public function pwd(Request $request) {
        return response()->json($this->cmd('pwd'));
    }

    private function getLocalRepo() {
        return dirname(base_path());
    }

    private function gitcmd(string $cmd ) {
        $re = [];
        $LOCAL_REPO = $this->getLocalRepo();
        chdir($LOCAL_REPO);
        $gitAuthUrl = '';
        $gitBranch = '';

        if (false && $cmd === 'git pull') {
            $queryGitBranch = $this->gitcmd('git branch --show-current');
            $gitBranch = trim($queryGitBranch['output']);

            $queryGitUrl = $this->gitcmd('git config --get remote.origin.url');
            $gitUrl = trim($queryGitUrl['output']);

            // $gitAuthUrl = str_replace('https://', 'https://ConeHead07:Ih1PvdPs%212312@', $gitUrl);
            // $gitAuthUrl = str_replace('https://', 'https://78eb140f0ef9bd8d1edd2585b0334b87061fc0d12@', $gitUrl);
            $gitAuthUrl = str_replace('https://', 'https://ConeHead07@', $gitUrl);
            // git pull https://78eb140f0ef9bd8d1edd2585b0334b87061fc0d12@github.com/ConeHead07/inventory.git
            // TEST 1.21
            // $cmd.= ' ' . $gitAuthUrl . ' ' . $gitBranch;
            $fullCmd = '/usr/bin/' . $cmd . ' ' . $gitAuthUrl . ' ' . $gitBranch . '  2>&1';
        } else {
            $fullCmd = '/usr/bin/' . $cmd . '  2>&1';
        }

        $re = $this->shellCmd($fullCmd);
        $re['fullCmd'] = $fullCmd;
        $re['LOCAL_REPO'] = $LOCAL_REPO;
        $re['gitBranch'] = $gitBranch;
        $re['gitAuthUrl'] = $gitAuthUrl;

        return $re;
    }

    private function cmd($command) {
        $output = '';
        $returnVar = '';
        $lastLine = exec($command, $output, $returnVar);

        return compact(
            'command',
            'output',
            'lastLine',
            'returnVar'
        );
    }


    private function shellCmd($command) {
        $output = '';
        $returnVar = '';
        $output = shell_exec($command);

        return compact(
            'command',
            'output'
        );
    }

    public function procOpenPull(Request $request, string $cmd = '') {
        $LOCAL_REPO = $this->getLocalRepo();
        // $this->gitcmd('git config --global credential.helper store');

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w"),  // stderr is a pipe that the child will write to
            // 2 => array("filee", "xyz.err.txt", "w")  // stderr is a file to write to
        );

        if (!$cmd) {
            $cmd = '/usr/bin/git pull';
        }

        $process = proc_open(
            $cmd,
            $descriptorspec,
            $pipes,
            $LOCAL_REPO
        );

        $aPromptAnswers = ["ConeHead07", "Ih1PvdPs!2312"];
        fwrite($pipes[0], implode("\n", $aPromptAnswers));
        $stdout = fread($pipes[1], 1024);
        $stderr = fread($pipes[2], 1024);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_status = proc_close($process);
        $success = ($exit_status === 0);

        return response()->json(compact(
            'LOCAL_REPO',
            'cmd',
            'success',
            'stdout',
            'stderr'
            ));
    }

}
