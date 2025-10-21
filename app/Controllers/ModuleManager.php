<?php

namespace App\Controllers;

use App\Models\Builtin\ModuleModel;

class ModuleManager extends BaseController
{
    public function index()
    {
        $data['title'] = 'Module Manager';
        return view('module_manager/index', $data);
    }
    
    public function addModule()
    {
        $data['title'] = 'Add New Module';
        return view('module_manager/add_module', $data);
    }
    
    public function saveModule()
    {
        $moduleModel = new ModuleModel();
        
        // Get form data
        $data = [
            'nama_module' => $this->request->getPost('nama_module'),
            'judul_module' => $this->request->getPost('judul_module'),
            'id_module_status' => $this->request->getPost('id_module_status'),
            'login' => $this->request->getPost('login'),
            'deskripsi' => $this->request->getPost('deskripsi')
        ];
        
        // Validate required fields
        if (empty($data['nama_module']) || empty($data['judul_module'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Module name and title are required'
            ]);
        }
        
        // Check if module name already exists
        $db = \Config\Database::connect();
        $existing = $db->table('module')
                      ->where('nama_module', $data['nama_module'])
                      ->countAllResults();
        
        if ($existing > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Module name already exists'
            ]);
        }
        
        // Insert module
        $result = $db->table('module')->insert($data);
        
        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Module added successfully',
                'id' => $db->insertID()
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to add module'
            ]);
        }
    }
    
    public function listModules()
    {
        $db = \Config\Database::connect();
        $modules = $db->table('module')
                     ->join('module_status', 'module.id_module_status = module_status.id_module_status', 'left')
                     ->orderBy('nama_module')
                     ->get()
                     ->getResultArray();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $modules
        ]);
    }
}
