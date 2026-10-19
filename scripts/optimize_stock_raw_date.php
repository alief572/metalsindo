<?php
if (PHP_SAPI !== 'cli') exit('CLI only');

class StockRawDateIndex
{
    private $db;
    private $host;
    private $name = 'idx_raw_snapshot_date_warehouse';
    public function __construct($db, $host) { $this->db=$db; $this->host=$host; }
    private function guard()
    {
        if (!in_array($this->host,array('localhost','127.0.0.1','metalsindo_db','db_metalsindo'),true)
            || $this->db->query('SELECT DATABASE()')->fetchColumn() !== 'metalsindo_dev')
            throw new RuntimeException('Hanya host lokal dan database metalsindo_dev yang diizinkan.');
    }
    private function indexes()
    {
        return $this->db->query('SHOW INDEX FROM stock_material_backup')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function preflight()
    {
        $this->guard();
        $definition=$this->db->query('SHOW CREATE TABLE stock_material_backup')->fetch(PDO::FETCH_NUM);
        if (strpos($definition[1],'ENGINE=InnoDB') === false) throw new RuntimeException('InnoDB diperlukan.');
        $indexes=$this->indexes(); $groups=array();
        foreach ($indexes as $index) {
            $groups[$index['Key_name']][(int)$index['Seq_in_index']] = $index['Sub_part'] === null && $index['Index_type'] === 'BTREE' ? $index['Column_name'] : null;
        }
        $covered=false;
        foreach ($groups as $columns) if (isset($columns[1],$columns[2]) && $columns[1]==='tgl' && $columns[2]==='id_gudang') $covered=true;
        return array('database'=>'metalsindo_dev','server_uuid'=>$this->db->query('SELECT @@server_uuid')->fetchColumn(),
            'covered'=>$covered,'indexes'=>$indexes);
    }
    public function apply($path)
    {
        $report=$this->preflight();
        if ($report['covered']) return array('created'=>0);
        foreach ($report['indexes'] as $index) if ($index['Key_name']===$this->name) throw new RuntimeException('Nama indeks sudah digunakan.');
        $manifest=array('version'=>1,'database'=>'metalsindo_dev','server_uuid'=>$report['server_uuid'],
            'index'=>$this->name,'columns'=>array('tgl','id_gudang'),'created'=>false,'before'=>$report);
        $file=fopen($path,'x');
        if (!$file) throw new RuntimeException('Manifest harus berkas baru.');
        $json=json_encode($manifest,JSON_PRETTY_PRINT);
        $written=fwrite($file,$json); fclose($file);
        if ($written!==strlen($json)) throw new RuntimeException('Gagal menyimpan manifest; indeks belum dibuat.');
        $this->db->exec('ALTER TABLE stock_material_backup ADD INDEX '.$this->name.' (tgl,id_gudang)');
        $manifest['created']=true;
        if (file_put_contents($path,json_encode($manifest,JSON_PRETTY_PRINT),LOCK_EX)===false)
            throw new RuntimeException('Indeks dibuat tetapi manifest gagal diperbarui; perlu audit manual sebelum rollback.');
        return array('created'=>1,'manifest'=>$path);
    }
    public function rollback($path)
    {
        $report=$this->preflight(); $manifest=json_decode(file_get_contents($path),true);
        if (!is_array($manifest) || !isset($manifest['version'],$manifest['database'],$manifest['server_uuid'],$manifest['index'],$manifest['columns'],$manifest['created'],$manifest['before']['indexes'])
            || $manifest['version']!==1 || $manifest['database']!=='metalsindo_dev' || $manifest['server_uuid']!==$report['server_uuid']
            || $manifest['index']!==$this->name || $manifest['columns']!==array('tgl','id_gudang')) throw new RuntimeException('Manifest tidak cocok.');
        foreach ($manifest['before']['indexes'] as $index) if ($index['Key_name']===$this->name) throw new RuntimeException('Indeks sudah ada sebelum apply.');
        if (!$manifest['created']) return array('dropped'=>0);
        $columns=array();
        foreach ($report['indexes'] as $index) if ($index['Key_name']===$this->name) {
            if ($index['Sub_part']!==null || $index['Index_type']!=='BTREE' || (int)$index['Non_unique']!==1) throw new RuntimeException('Definisi indeks telah berubah.');
            $columns[(int)$index['Seq_in_index']]=$index['Column_name'];
        }
        if (!$columns) return array('dropped'=>0);
        if ($columns!==array(1=>'tgl',2=>'id_gudang')) throw new RuntimeException('Definisi indeks telah berubah.');
        $this->db->exec('ALTER TABLE stock_material_backup DROP INDEX '.$this->name);
        return array('dropped'=>1);
    }
}

if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '')===__FILE__) {
    try {
        $options=getopt('',array('apply','rollback:','manifest:'));
        $host=getenv('RAW_DB_HOST');
        if (!in_array($host,array('localhost','127.0.0.1','metalsindo_db','db_metalsindo'),true)) throw new RuntimeException('RAW_DB_HOST harus host lokal yang diizinkan.');
        $db=new PDO('mysql:host='.$host.';dbname=metalsindo_dev;charset=utf8',getenv('RAW_DB_USER'),getenv('RAW_DB_PASSWORD'),array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        $installer=new StockRawDateIndex($db,$host);
        if (isset($options['apply'],$options['rollback'])) throw new RuntimeException('Pilih apply atau rollback.');
        if (isset($options['apply'])) {
            if (empty($options['manifest'])) throw new RuntimeException('--manifest wajib untuk apply.');
            $result=$installer->apply($options['manifest']);
        } elseif (isset($options['rollback'])) $result=$installer->rollback($options['rollback']);
        else $result=$installer->preflight();
        echo json_encode($result,JSON_PRETTY_PRINT),"\n";
    } catch (Exception $e) { fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
}
