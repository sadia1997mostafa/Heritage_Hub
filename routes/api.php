use App\Http\Controllers\HeritageController;

Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'show']);
