<?php
class GIFModel
{
    private $dbc;
    private $config;

    public function __construct()
    {
        $this->dbc = bsFactory::get('pdo')->get();
        $this->config = bsFactory::get('config');
    }

    public function count()
    {
        $st = $this->dbc->query('SELECT COUNT(*) AS cnt FROM gifs');
        $rs = $st->fetch(PDO::FETCH_ASSOC);
        return (int)$rs['cnt'];
    }

    public function count_by_author($author)
    {
        $st = $this->dbc->prepare('SELECT COUNT(*) AS cnt FROM gifs WHERE gif_author=:author');
        $st->execute(array(':author' => $author));
        $rs = $st->fetch(PDO::FETCH_ASSOC);
        if ($rs && is_array($rs)) {
            return (int)$rs['cnt'];
        }

        return 0;
    }

    public function get_authors()
    {
        $st = $this->dbc->query('SELECT DISTINCT gif_author FROM gifs ORDER BY gif_author');
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $authors = array();
        if ($rs && is_array($rs) && count($rs)) {
            foreach ($rs as $row) {
                $authors[] = $row['gif_author'];
            }
        }

        return $authors;
    }

    public function get_paged($page)
    {
        $page = ((int)$page)-1;
        $size = $this->config->page_size;

        if ($page < 0 || $page >= ceil($this->count() / $size)) {
            throw new bsException('Page out of database range');
        }

        $st = $this->dbc->query('SELECT * FROM gifs ORDER BY gif_ts DESC LIMIT '.($page * $size).','.$size);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_paged_by_author($author, $page)
    {
        $page = ((int)$page)-1;
        $size = $this->config->page_size;

        if ($page < 0 || $page >= ceil($this->count_by_author($author) / $size)) {
            throw new bsException('Page out of database range');
        }

        $st = $this->dbc->prepare('SELECT * FROM gifs WHERE gif_author=:author ORDER BY gif_ts DESC LIMIT '.($page * $size).','.$size);
        $st->execute(array(':author' => $author));
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists_by_img($img)
    {
        $st = $this->dbc->query('SELECT * FROM gifs WHERE gif_img=:img');
        $st->execute(array(':img' => $img));
        $rs = $st->fetchAll($st, PDO::FETCH_ASSOC);

        return ($rs && is_array($rs) && count($rs));
    }

    public function add_from_img($img)
    {
        $insert = array();
        $urlparts = parse_url($img);
        $parts = explode('/', $urlparts['path']);
        preg_match_all('#^(\d+).gif$#', end($parts), $ts);
        if (isset($ts[1], $ts[1][0])) {
            $insert[':ts'] = $ts[1][0];
            $insert[':author'] = prev($parts);
            $insert[':img'] = $img;
            $insert[':source'] = file_get_contents(str_replace('.gif', '.txt', $img));

            $gifs_st = $this->dbc->prepare('SELECT * FROM gifs WHERE gif_img=:img');
            $gifs_st->execute(array(':img' => $img));
            $gifs = $gifs_st->fetchAll(PDO::FETCH_ASSOC);

            if (count($gifs)) {
                return false;
            }
            else {
                $st = $this->dbc->prepare('INSERT INTO gifs(gif_ts, gif_author, gif_img, gif_source) values(:ts, :author, :img, :source)');
                $st->execute($insert);
                return true;
            }
        }
    }
}

