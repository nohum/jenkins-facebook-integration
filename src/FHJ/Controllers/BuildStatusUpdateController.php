<?php

namespace FHJ\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FHJ\Entities\Project;

/**
 * BuildStatusUpdateController
 * @package FHJ\Controllers
 */
class BuildStatusUpdateController extends BaseController {

    const ROUTE_UPDATE_STATUS = 'updateStatus';

    public function updateLastBuildStatusAction(Request $request, $secretKey) {
        $project = $this->getProjectRepository()->findProjectBySecretKey($secretKey);
        if ($project === null) {
            $this->getLogger()->addInfo(sprintf('no project found for secret key "%s"', $secretKey));
            
            throw new NotFoundHttpException('invalid secret key');
        }
        
        $requestContent = $request->getContent();
        if ($requestContent === false) {
            throw new \Exception('content of request could not be read');
        }
        
        $jsonContent = json_decode($requestContent);
        if ($jsonContent === null) {
            throw new \Exception(sprintf('json decode error. last error: "%s"', json_last_error_msg()));
       } 
        
        /* Example input:
        {"name":"JobName",
         "url":"JobUrl",
         "build":{"number":1,
        	  "phase":"STARTED",
        	  "status":"FAILED",
                  "url":"job/project/5",
                  "full_url":"http://ci.jenkins.org/job/project/5"
                  "parameters":{"branch":"master"}
        	 }
        }
        */
        
        if (!property_exists($jsonContent, 'build') || !property_exists($jsonContent->build, 'phase')
                || !property_exists($jsonContent->build, 'status')) {
            throw new \Exception('received incomple Jenkins status json');
        }
        
        // We are only interested in the result if the build execution has been finished
        if ($jsonContent->build->phase !== 'FINISHED') {
            $this->getLogger()->addDebug(sprintf(
                'status update: throwing away status "%s" in phase "%s" for project id "%d"',
                $jsonContent->build->status, $jsonContent->build->phase, $project->getId()));
            
            return new Response(); // 200 OK
        }
        
        $currentStatus = strtoupper($jsonContent->build->status);
        if ($project->getLastBuildState() === $currentStatus) {
            return new Response(); // 200 OK
        }
        
        try {
            // send an event
            
            $project->setLastBuildState($currentStatus);
            $this->getProjectRepository()->updateProject($project);
        } catch (\Exception $e) {
            $this->getLogger()->addDebug(sprintf(
                'status update: error at updating build status to "%s" for project id "%d"',
                $jsonContent->build->status, $project->getId()));
        } 
    }

}
