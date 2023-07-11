<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ChubV\PHPSTL\Model\STLModel;
use App\Models\Upload;

class STLAnalysisController extends Controller
{
    public function showForm()
    {
        return view('example');
    }

    public function analyzeSTL(Request $request)
    {
        $stlFile = $request->file('stlFile');

        // Log the uploaded file
        $upload = new Upload();
        $upload->file_original_name = $stlFile->getClientOriginalName();
        $upload->file_name = $stlFile->hashName();
        $upload->file_size = $stlFile->getSize();
        $upload->extension = $stlFile->getClientOriginalExtension();
        $upload->type = 'stl'; // Assuming the uploaded file is an STL file
        $upload->save();

        // Load the STL file and extract the necessary data
        $stlModel = new STLModel();
        $stlModel->load($stlFile->getPathname());

        // Get the dimensions of the model
        $dimensions = $stlModel->getDimensions();

        // Get the model volume, machine space, support structure, parts bounds volume, and part count
        $modelVolume = $stlModel->getVolume();
        $machineSpace = $stlModel->getMachineSpace();
        $supportStructure = $stlModel->getSupportStructure();
        $partsBoundsVolume = $stlModel->getPartsBoundsVolume();
        $partCount = $stlModel->getPartCount();

        // Return the analyzed data to the view
        return view('stl_analysis_result', [
            'dimensions' => $dimensions,
            'modelVolume' => $modelVolume,
            'machineSpace' => $machineSpace,
            'supportStructure' => $supportStructure,
            'partsBoundsVolume' => $partsBoundsVolume,
            'partCount' => $partCount
        ]);
    }
}
