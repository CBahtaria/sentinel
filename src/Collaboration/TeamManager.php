<?php  
/**  
 * Multi-User Collaboration Manager  
 */  
class TeamManager {  
    private $pdo;  
    public function __construct() {  
        $this- = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');  
    }  
    public function createTeam($name, $leader_id) {  
        $stmt = $this- 
        return $stmt-, $leader_id]);  
    }  
    public function addMember($team_id, $user_id) {  
        $stmt = $this- 
        return $stmt-, $user_id]);  
    }  
}  
?>  
