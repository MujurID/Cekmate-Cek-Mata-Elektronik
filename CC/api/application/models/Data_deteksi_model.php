<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_deteksi_model extends CI_Model {

    public function __construct() {
       parent::__construct();
       $this->load->database();
    }

    /*public function list() {
        $query = $this->db->get("data_deteksi")->result();
        return $query;
    }*/

    public function list($users_id = 0)
	{
        $query = $this->db->get_where("data_deteksi", ['users_id' => $users_id])->result();
        return $query;
	}

	public function show($id = 0)
	{
        $query = $this->db->get_where("data_deteksi", ['id' => $id])->row_array();
        return $query;
	}
      

    public function insert($foto_mata_sebelum, $users_id)
    {
        $data = array(
			'foto_mata_sebelum'   => $foto_mata_sebelum,
			'users_id'      => $users_id,
		);
        $this->db->insert('data_deteksi',$data);
        return $this->db->insert_id(); 
    } 
     
    /*public function update($data, $id)
    {
        $data = $this->db->update('data_deteksi', $data, array('id'=>$id));
        //echo $this->db->last_query();
		return $this->db->affected_rows();
    }*/
     
    public function delete($id)
    {
        $this->db->delete('data_deteksi', array('id'=>$id));
        return $this->db->affected_rows();
    }
}
