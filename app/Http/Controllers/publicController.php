<?php

namespace App\Http\Controllers;

use Alaouy\Youtube\Facades\Youtube;
use App\Model\Tempfile;
use App\Model\Video;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;


use Spatie\MediaLibrary\MediaCollections\Models\Media;

class publicController extends Controller {

	public function hi() {
		
		// $v = Video::find(2931);
		// dd($v->getFirstMediaUrl('video') );
		// $f = Tempfile::find(1144);
		// $mediaItem = $f->getMedia('tempFile')->first();
		// // dd($mediaItem);
		// // dd($f->getFirstMediaURL('tempFile'));

		// // $temp = Tempfile::create();
        // // $mm = $temp->addMediaFromUrl($f->getFirstMediaURL('tempFile'))->toMediaCollection('tempFile', 'dropbox');
        // // $image = $temp->getFirstMediaURL('tempFile');
		// // dd($temp);
		
		// $copiedMediaItem = $mediaItem->move($f, 'tempFile', 'dropbox');
		// // $copiedMediaItem = $mediaItem->move($f, 'tempFile');
		// dd($copiedMediaItem);


		// $media = Media::where( 'collection_name', 'tempFile' )->get();
		// foreach ( $media as $m ) {
		// 	$m->delete();
		// }
		// return 1;
		$files = Video::whereBetween( 'id', array( 1, 3382 ) )->where('backedUp',0)->get();
		dd(count($files));
        
		foreach ( $files as $key => $file ) {
			$v = $file->getFirstMedia( 'video' );
			// dd($v);
			if ( !$v ) {
				continue;
			}
			$media = Media::find( $v->id );
			if ( $media->disk == 'public2' ) {
				continue;
			}
			
			$directory_path = pathinfo( $file->getFirstMediaPath( 'video' ), PATHINFO_DIRNAME );
			$diskpath       = Storage::disk( 'public2' )->path( '' );
			$to             = $diskpath . basename( $directory_path );
			$fileSystem     = new Filesystem();
			$ss             = $fileSystem->copyDirectory( $directory_path, $to, true );
			if ($ss){
				$dd             = $fileSystem->deleteDirectory( $directory_path);
				$media->disk             = 'public2';
				$media->conversions_disk = 'public2';
				$media->update();
			}
		}

		dd( 11 );

		// return Tempfile::all()->delete();
		//
		

		// refresh token
		// try {
		// $response = Http::withHeaders([
		// 'Authorization' => "Basic ".base64_encode('5c7tl_YDQFiS0TELE_Kbdg:VvHk8JRpYUlScvH5uekJoAJtyZXcnx5I'),
		// ])->post('https://zoom.us/oauth/token?grant_type=refresh_token&refresh_token=eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiJjYWNhOGFjMC04YWE3LTQwODQtODViMC04NjNiNGQ1MjllYWYifQ.eyJ2ZXIiOjcsImF1aWQiOiIwYWMxZGY2NTRiNWY4MDQ4Njk3YzZhNTMyMWZiMWY1OSIsImNvZGUiOiJJSUxZTUdpUG9pXzZ0VlRWeUFoVEF1QTZfRzhaUU9JbUEiLCJpc3MiOiJ6bTpjaWQ6NWM3dGxfWURRRmlTMFRFTEVfS2JkZyIsImdubyI6MCwidHlwZSI6MSwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiI2dFZUVnlBaFRBdUE2X0c4WlFPSW1BIiwibmJmIjoxNjA5NTE1MTQyLCJleHAiOjIwODI1NTUxNDIsImlhdCI6MTYwOTUxNTE0MiwiYWlkIjoiVUpWVkFUbkRUTE9jMXA5WDZpcGN3QSIsImp0aSI6IjA2NWY3MzE1LTNkMTItNGYxZi1hYTVhLTBjOWQyYWUwZjI0NyJ9.eYbEo725sWvA9UNpPj5stBm_NoMENqtQ3qncLevFtoetXI4HUEWGoFoNEc1-L3F0Kj6ctnbvhSgzYhtYIaKlnQ');
		//
		// dd($response->json());
		// } catch (Exception $e) {
		// echo $e->getMessage();
		// }
		//

		// Create
		try {
			$response = Http::withHeaders(
				array(
					'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiI2YmFkMTdhNC04NWY1LTQ0MjAtYTgxYS01ZWJmMWRhNDJlM2UifQ.eyJ2ZXIiOjcsImF1aWQiOiIwYWMxZGY2NTRiNWY4MDQ4Njk3YzZhNTMyMWZiMWY1OSIsImNvZGUiOiJJSUxZTUdpUG9pXzZ0VlRWeUFoVEF1QTZfRzhaUU9JbUEiLCJpc3MiOiJ6bTpjaWQ6NWM3dGxfWURRRmlTMFRFTEVfS2JkZyIsImdubyI6MCwidHlwZSI6MCwidGlkIjoxLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiI2dFZUVnlBaFRBdUE2X0c4WlFPSW1BIiwibmJmIjoxNjA5NTIxMTI2LCJleHAiOjE2MDk1MjQ3MjYsImlhdCI6MTYwOTUyMTEyNiwiYWlkIjoiVUpWVkFUbkRUTE9jMXA5WDZpcGN3QSIsImp0aSI6ImI2NTQ1YzRhLTI0MGEtNGM3ZC1hNGQ1LWUyNTA1M2JjMDRlMiJ9.t5NA2bi6VPnc0b10a3LL_9d3kTR2tBcDQcr_qUJT8t4Q4RFo2a_HPlMdOydu7urNSStRC17Td2GZni5zckKbew',
				)
			)->post(
				'https://api.zoom.us/v2/users/me/meetings',
				array( 'agenda' => 'string' )
			);
			dd( $response->json() );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		// list all
		try {
			$response = Http::withHeaders(
				array(
					'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiI4ZTlmZTBmMC03MzRiLTQyODctYTRjMC05MWVmZDE2ZTRjODQifQ.eyJ2ZXIiOjcsImF1aWQiOiIwYWMxZGY2NTRiNWY4MDQ4Njk3YzZhNTMyMWZiMWY1OSIsImNvZGUiOiJJSUxZTUdpUG9pXzZ0VlRWeUFoVEF1QTZfRzhaUU9JbUEiLCJpc3MiOiJ6bTpjaWQ6NWM3dGxfWURRRmlTMFRFTEVfS2JkZyIsImdubyI6MCwidHlwZSI6MCwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiI2dFZUVnlBaFRBdUE2X0c4WlFPSW1BIiwibmJmIjoxNjA5NTE1MTQyLCJleHAiOjE2MDk1MTg3NDIsImlhdCI6MTYwOTUxNTE0MiwiYWlkIjoiVUpWVkFUbkRUTE9jMXA5WDZpcGN3QSIsImp0aSI6IjljNjU5YTkzLTczMDEtNDE4Zi04M2UwLWZiODdjY2FjNzAxYyJ9.i6vcO_x-_eARFHE5udQuc3GuGVp0cOctH-uYIQfitu5bu7kSCHPx9vxpYqKWenAKPS7jQ_AAeiM9SKNmjS93Kg',
				)
			)->get( 'https://api.zoom.us/v2/users/me/meetings' );
			dd( $response->json() );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		// Get a meeting
		// try {
		// $response = Http::withHeaders([
		// 'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiI4ZTlmZTBmMC03MzRiLTQyODctYTRjMC05MWVmZDE2ZTRjODQifQ.eyJ2ZXIiOjcsImF1aWQiOiIwYWMxZGY2NTRiNWY4MDQ4Njk3YzZhNTMyMWZiMWY1OSIsImNvZGUiOiJJSUxZTUdpUG9pXzZ0VlRWeUFoVEF1QTZfRzhaUU9JbUEiLCJpc3MiOiJ6bTpjaWQ6NWM3dGxfWURRRmlTMFRFTEVfS2JkZyIsImdubyI6MCwidHlwZSI6MCwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiI2dFZUVnlBaFRBdUE2X0c4WlFPSW1BIiwibmJmIjoxNjA5NTE1MTQyLCJleHAiOjE2MDk1MTg3NDIsImlhdCI6MTYwOTUxNTE0MiwiYWlkIjoiVUpWVkFUbkRUTE9jMXA5WDZpcGN3QSIsImp0aSI6IjljNjU5YTkzLTczMDEtNDE4Zi04M2UwLWZiODdjY2FjNzAxYyJ9.i6vcO_x-_eARFHE5udQuc3GuGVp0cOctH-uYIQfitu5bu7kSCHPx9vxpYqKWenAKPS7jQ_AAeiM9SKNmjS93Kg',
		// ])->get('https://api.zoom.us/v2/users/me/meetings');
		// dd($response->json());
		// } catch (Exception $e) {
		// echo $e->getMessage();
		// }
	}
}
