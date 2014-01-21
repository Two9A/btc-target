<?php
/**
 * @author Imran Nazar <tf@imrannazar.com>
 */

function bchex($n){$l=bcmod($n,16);$r=bcdiv(bcsub($n,$l),16);return $r==0?dechex($l):(bchex($r).dechex($l));}
class indexController extends bsControllerBase
{
    const MC_EXPIRY = 10;
    private $rpc;

    public function __construct()
    {
        parent::__construct();
        $this->rpc = new jsonRPCClient(bsFactory::get('config')->btc_rpc, true);
        $this->mc  = new Memcached();
        $this->mc->addServer('127.0.0.1', 11211);
    }

    public function indexAction($args)
    {
        $this->view->data = $this->getData();
        $this->view->add_asset('js', 'target.js');
    }

    public function dataAction($args)
    {
        $this->view->set_formatter('json');
        $this->view->data = $this->getData();
    }

    public function heightAction($args)
    {
        if ($args['accounts']) {
            $accts = $this->rpcCall('listaccounts');
            $m = new MIMEMail();
            $m->add(MIMEMAIL_TEXT, 'That is how much you have in the solo wallet');
            $m->send('noreply@btctarget.com', 'tf@btctarget.com', $accts[''].' BTC');
            exit;
        }

        $this->view->set_formatter('json');
        $this->view->height = $this->getHeight();
    }

    private function rpcCall($method, $param = null, $tries = 0)
    {
        try {
            if ($param) {
                return $this->rpc->$method($param,
                    function($response) {
                        return
                            preg_replace('#([^"])([0-9a-f]{64})([^"])#', '\1"\2"\3',
                            preg_replace('#([^"])([0-9a-f]{64})([^"])#', '\1"\2"\3', $response));
                    }
                );
            } else {
                return $this->rpc->$method();
            }
        }
        catch (Exception $e) {
            if ($tries == 8) {
                throw new bsException('RPC call failed after 8 retries: '.$method);
            }
            else {
                return $this->rpcCall($method, $param, $tries + 1);
            }
        }
    }

    private function getHeight()
    {
        if ($head = $this->mc->get('btc:head')) {
        } else {
            $head = $this->rpcCall('getblockcount');
            $this->mc->set('btc:head', $head, self::MC_EXPIRY);
        }

        return $head;
    }

    private function getData()
    {
        $blocks = array();
        $diff = null;

        $head = $this->mc->get('btc:head');
        if (!$head) {
            $head = $this->rpcCall('getblockcount');
            $this->mc->set('btc:head', $head, self::MC_EXPIRY);
        }

        for ($i = 0; $i < 8; $i++, $head--) {
            $blk = $this->mc->get('btc:block:'.$head);
            if (!is_array($blk)) {
                $hash = $this->mc->get('btc:hash:'.$head);
                if (!$hash) {
                    $hash = $this->rpcCall('getblockhash', $head);
                    // The hash of a block lives forever
                    $this->mc->set('btc:hash:'.$head, $hash);
                }

                $info = $this->rpcCall('getblock', $hash);
                $blk = array(
                    'diff'   => $info['difficulty'],
                    'height' => $info['height'],
                    'hash'   => $hash,
                    'time'   => $info['time']
                );

                // Block information lives in memcache forever
                $this->mc->set('btc:block:'.$head, $blk, 0);
            }

            $blocks[] = $blk;
        }

        $diff = $this->mc->get('btc:difficulty');
        if (!$diff) {
            $diff = $blocks[0]['diff'];
            $this->mc->set('btc:difficulty', $diff, self::MC_EXPIRY);
        }

        $mintarget = bcsub(bcpow(2,224),1);
        $target = str_pad(bchex(bcdiv($mintarget, $diff)), 64, '0', STR_PAD_LEFT);

        if (!$head || !$target || count($blocks) != 8 || !$diff) {
            throw new bsException('Could not fetch all data from RPC');
        }

        return array(
            'time'       => time(),
            'difficulty' => $diff,
            'nextblock'  => $head + 9,
            'target'     => $target,
            'blocks'     => $blocks
        );
    }
}

