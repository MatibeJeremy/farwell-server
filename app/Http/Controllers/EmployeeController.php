<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;

class EmployeeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/upload",
     *     summary="Upload and process employee data file",
     *     tags={"Employee"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded and processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="File uploaded and processed successfully!"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found after upload",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="File not found after upload."),
     *             @OA\Property(property="path", type="string", example="path/to/file")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="File upload failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="File upload failed."),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048',
        ]);

        try {
            // Store the uploaded file
            $path = $request->file('file')->store('uploads');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Debugging: Check if the file exists
        $fullPath = storage_path("app/private/{$path}");
        print_r("File path", $fullPath);
        if (!file_exists($fullPath)) {
            return response()->json([
                'message' => 'File not found after upload.',
                'path' => $fullPath,
            ], 404);
        }

        // Process the uploaded file using Excel
        $data = Excel::toArray(new EmployeeImport, $fullPath);

        // Cache the processed data
        if (!empty($data[0])) {
            Cache::put('employees_data', $data[0], now()->addMinutes(10));
        }

        return response()->json([
            'message' => 'File uploaded and processed successfully!',
            'data' => $data[0],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/employees",
     *     summary="Retrieve employee data",
     *     tags={"Employee"},
     *     @OA\Response(
     *         response=200,
     *         description="Employee data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getEmployees()
    {
        $employees = Cache::get('employees_data', []);

        return response()->json([
            'data' => $employees,
        ]);
    }
}
