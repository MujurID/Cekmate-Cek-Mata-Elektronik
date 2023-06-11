<?php

require APPPATH . '/libraries/REST_Controller.php';

require_once "vendor/autoload.php";
        
use Google\Cloud\Storage\StorageClient;
use Restserver\Libraries\REST_Controller;

class Data_deteksi extends REST_Controller
{

    /**
     * CONSTRUCTOR | LOAD MODEL
     *
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Authorization_Token');
        $this->load->model('Data_deteksi_model');
        $this->load->helper('api_helper');
        $this->load->library('form_validation');
    }

    /**
     * SHOW | GET method.
     *
     * @return Response
     */
    public function list_get()
    {
        check_authorization();

        $token_validation = $this->authorization_token->validateToken();
        $users_id = $token_validation["data"]->uid;

        $data = $this->Data_deteksi_model->list($users_id);

        $this->response([
            'status' => TRUE,
            'data' => $data
        ], REST_Controller::HTTP_OK);
    }

    public function show_get($id = 0)
    {
        check_authorization();

        if (empty($id)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Data deteksi id param not found.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = $this->Data_deteksi_model->show($id);

        $token_validation = $this->authorization_token->validateToken();
        $users_id = $token_validation["data"]->uid;

        if (empty($data)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Data deteksi not found.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if ($data["users_id"]!==$users_id) {
            $this->response([
                'status' => FALSE,
                'message' => 'Data deteksi not found.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $this->response([
            'status' => TRUE,
            'data' => $data
        ], REST_Controller::HTTP_OK);
    }

    /**
     * INSERT | POST method.
     *
     * @return Response
     */
    public function insert_post()
    {
        check_authorization();

        $token_validation = $this->authorization_token->validateToken();
        $users_id = $token_validation["data"]->uid;

        //$input = $this->input->post();

        #$foto_mata_sebelum = $this->input->post('foto_mata_sebelum');
        $foto_mata_sebelum_name = $_FILES['foto_mata_sebelum']['name'];
        $foto_mata_sebelum_tmp_name = $_FILES['foto_mata_sebelum']['tmp_name'];

        
        try {
            $storage = new StorageClient([
                'keyFilePath' => 'cekmate-0f1c195007f5.json',
            ]);
        
            $bucketName = 'cekmate_data_deteksi';
            #$fileName = $foto_mata_sebelum_name;
            $fileName = date('Y-m-d_H-i-s',time()) . '_' . $users_id . '.' . pathinfo($foto_mata_sebelum_name, PATHINFO_EXTENSION);
            $bucket = $storage->bucket($bucketName);
            $object = $bucket->upload(
                fopen($foto_mata_sebelum_tmp_name, 'r'),
                [
                    'name' => $fileName,
                ]
            );
            //echo "File uploaded successfully. File path is: https://storage.googleapis.com/$bucketName/$fileName";

            $foto_mata_sebelum = $fileName;
            $status_penyakit = 0; // Set the initial status_penyakit value to 0
            $status_deteksi = 0; // Set the initial status_deteksi value to 0

            // Store the result in the MySQL database
            $databaseHost = '34.128.119.108';
            $databaseName = 'cekmate-mysql';
            $databaseUsername = 'root';
            $databasePassword = '';

            $conn = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "INSERT INTO data_deteksi (foto_mata_sebelum, user_id, status_penyakit, status_deteksi) 
                    VALUES ('$foto_mata_sebelum', $users_id, $status_penyakit, $status_deteksi)";

            if ($conn->query($sql) === TRUE) {
                $conn->close();

                $this->response([
                    'status' => TRUE,
                    'message' => 'Data deteksi created successfully.'
                ], REST_Controller::HTTP_OK);
            } else {
                $conn->close();

                $this->response([
                    'status' => FALSE,
                    'message' => 'Failed to store data in the database.'
                ], REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            // ...

            $this->response([
                'status' => FALSE,
                'message' => $e->getMessage()
            ], REST_Controller::HTTP_OK);
        }
    }



    }

    /**
     * UPDATE | PUT method.
     *
     * @return Response
     */
    /*public function update_post($id)
    {
        check_authorization();

        $this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[3]|max_length[20]');
        $this->form_validation->set_rules('price', 'Price', 'required|decimal');

        if (!$this->form_validation->run()) {
            $this->response([
                'status' => FALSE,
                'message' => 'Validation Error.',
                'errors' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $input = $this->input->post();
        $response = $this->Data_deteksi_model->update($input, $id);

        if (empty($response)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Not updated'
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => TRUE,
            'message' => 'Data deteksi updated successfully.'
        ], REST_Controller::HTTP_OK);
    }*/

    /**
     * DELETE method.
     *
     * @return Response
     */
    public function delete_delete($id)
    {
        check_authorization();

        $data = $this->Data_deteksi_model->show($id);

        $token_validation = $this->authorization_token->validateToken();
        $users_id = $token_validation["data"]->uid;

        if ($data["users_id"]!==$users_id) {
            $this->response([
                'status' => FALSE,
                'message' => 'Data deteksi not found.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }else{
            $response = $this->Data_deteksi_model->delete($id);

            if (empty($response)) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Not deleted'
                ], REST_Controller::HTTP_NOT_FOUND);
            }

            $this->response([
                'status' => TRUE,
                'message' => 'Data deteksi deleted successfully.'
            ], REST_Controller::HTTP_OK);
        }
    }
}
