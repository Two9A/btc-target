<?php
class AvgModel
{
    private $dbc;
    private $config;

    public function __construct()
    {
        $this->dbc = bsFactory::get('pdo')->get();
        $this->config = bsFactory::get('config');
    }

    public function get_by_currency($currency)
    {
        $st = $this->dbc->prepare('SELECT value FROM avg WHERE currency=:curr ORDER BY ts ASC');
        $st->execute(array(':curr' => $currency));

        $out = array();
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = $row['value'];
        }
        return $out;
    }

    public function push($input)
    {
        if ($input && count($input)) {
            $st = $this->dbc->prepare('INSERT INTO avg(ts, currency, value) values(:ts, :curr, :last)');
            $ts = strtotime(date('Y-m-d H:i:00', time()));
            foreach ($input as $currency => $values) {
                $st->execute(array(':ts' => $ts, ':curr' => $currency, ':last' => $values['last']));
            }

            if ($this->get_ts_count() > 10) {
                $st = $this->dbc->prepare('DELETE FROM avg WHERE ts=:ts');
                $st->execute(array(':ts' => $this->get_oldest_ts()));
            }
        }
    }

    public function get_ts_count()
    {
        $st = $this->dbc->query('SELECT COUNT(DISTINCT ts) AS cnt FROM avg ORDER BY ts ASC LIMIT 1');
        $rs = $st->fetch(PDO::FETCH_ASSOC);
        return $rs['cnt'];
    }

    public function get_oldest_ts()
    {
        $st = $this->dbc->query('SELECT ts FROM avg ORDER BY ts ASC LIMIT 1');
        $rs = $st->fetch(PDO::FETCH_ASSOC);
        return $rs['ts'];
    }

    public function get_newest_ts()
    {
        $st = $this->dbc->query('SELECT ts FROM avg ORDER BY ts DESC LIMIT 1');
        $rs = $st->fetch(PDO::FETCH_ASSOC);
        return $rs['ts'];
    }
}

