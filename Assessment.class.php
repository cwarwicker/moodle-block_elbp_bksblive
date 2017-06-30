<?php
namespace ELBP\Plugins\elbp_bksblive;


class Assessment {
    
    const LIMIT = 25; // Limit results to this maximum no. records
    private $connection = false;
    
    public function __construct() {
        ;
    }
    
    public function loadConnection($connection){
        $this->connection = $connection;
    }
    
   
    
    /**
     * Get all the Initial Assessment results for this user and assessmentid
     * @param type $username
     * @param type $assessmentID
     * @return type
     */
    public function getUserAllIA($username, $assessmentID){
        
        if (is_array($assessmentID)){
            
            $new = $assessmentID[0];
            $old = $assessmentID[1];
            
            $sql = "SELECT [SessionID], [userName], [Result_DisplayLong] as 'Result', [Result_Rank],
                            CAST( [Date] as char(20) ) as 'Date', [Subject], 
                            DATEDIFF(s, '19700101', [Date]) as ts
                        FROM [bksb_InitialAssessmentResults]
                        WHERE [userName] LIKE ? AND ([Assessment_ID] LIKE ? OR [Assessment_ID] LIKE ?)
                        ORDER BY [Date] DESC";
            
            $params = array($username, $new, $old);
            
        } else {
        
            $sql = "SELECT [SessionID], [userName], [Result_DisplayLong] as 'Result', [Result_Rank],
                            CAST( [Date] as char(20) ) as 'Date', [Subject], 
                            DATEDIFF(s, '19700101', [Date]) as ts
                        FROM [bksb_InitialAssessmentResults]
                        WHERE [userName] LIKE ? AND [Assessment_ID] LIKE ?
                        ORDER BY [Date] DESC";
            
            $params = array($username, $assessmentID);
        
        }
        
        $results = $this->connection->query($sql, $params);
        return $this->connection->fetchAll($results);

    }
    
    /**
     * Get the latest result for this user and initial assessment id
     * @param type $username
     * @param type $assessmentID
     * @return type
     */
    public function getUserLatestIA($username, $assessmentID, $filters = false){
        
        $params = array($username, $assessmentID);
        
        $dateFilter = (isset($filters['startdate']) && $filters['startdate'] > 0) ? true : false;
        
        // If comparing dates, we need to get them all then loop through and find the first one that
        // meets that date comparison. Otherwise just get first 1
        $top = ($dateFilter) ? "" : "TOP 1";
        
        $sql = "SELECT {$top} 
                        [SessionID], [userName], [Result_DisplayLong] as 'Result', [Result_Rank],
                        CAST( [Date] as char(20) ) as 'Date', [Subject], 
                        DATEDIFF(s, '19700101', [Date]) as ts
                    FROM [bksb_InitialAssessmentResults]
                    WHERE [userName] LIKE ? AND [Assessment_ID] LIKE ?";
        
          // Can't get date comparisons to work with linux freetds driver, and native client sql server driver just has more problems
//        if (isset($filters['startdate']) && $filters['startdate'] > 0){
//            $sql .= " AND DATEDIFF(s, '19700101', [Date]) >= ? ";
//            $params[] = $filters['startdate'];
//        }
        
        $sql .= " ORDER BY [Date] DESC";
                
        $query = $this->connection->query($sql, $params);
        
        if ($dateFilter){
            
            $result = false;
            $results = $this->connection->fetchAll($query);
            if ($results){
                
                foreach($results as $res){
                    
                    if ($res['ts'] >= $filters['startdate']){
                        $result = (object)$res;
                        break;
                    }
                    
                }
                
            }
            
            
        } else {
            
            $result = $this->connection->fetch($query);
            if ($result){
                $result = (object)$result;
            }
            
        }
        
        
        return $result;
        
    }
    
    
    /**
     * Get the latest result for this user and initial assessment id
     * @param type $username
     * @param type $assessmentID
     * @return type
     */
    public function getUserBestIA($username, $assessmentID, $filters = false){
        
        if (!$assessmentID) return false;
        
        $dateFilter = (isset($filters['startdate']) && $filters['startdate'] > 0) ? true : false;
        
        // If comparing dates, we need to get them all then loop through and find the first one that
        // meets that date comparison. Otherwise just get first 1
        $top = ($dateFilter) ? "" : "TOP 1";
                        
        if (is_array($assessmentID)){
            
            $new = $assessmentID[0];
            $old = $assessmentID[1];
            
            $params = array($username, $new, $old);
            
            $sql = "SELECT {$top} 
                            [SessionID], [userName], [Result_DisplayLong] as 'Result', [Result_Rank],
                            CAST( [Date] as char(20) ) as 'Date', [Subject], 
                            DATEDIFF(s, '19700101', [Date]) as ts
                        FROM [bksb_InitialAssessmentResults]
                        WHERE [userName] LIKE ? AND ([Assessment_ID] LIKE ? OR [Assessment_ID] LIKE ?) ";
            
            // Can't get date comparisons to work with linux freetds driver, and native client sql server driver just has more problems
//            if (isset($filters['startdate']) && $filters['startdate'] > 0){
//                $sql .= " AND DATEDIFF(s, '19700101', [Date]) >= ? ";
//                $params[] = $filters['startdate'];
//            }
            
            $sql .= " ORDER BY [Result_Rank] ASC";
            
            
            
        } else {
            
            $params = array($username, $assessmentID);
        
            $sql = "SELECT {$top} 
                            [SessionID], [userName], [Result_DisplayLong] as 'Result', [Result_Rank],
                            CAST( [Date] as char(20) ) as 'Date', [Subject], 
                            DATEDIFF(s, '19700101', [Date]) as ts
                        FROM [bksb_InitialAssessmentResults]
                        WHERE [userName] LIKE ? AND [Assessment_ID] LIKE ? ";
            
            // Can't get date comparisons to work with linux freetds driver, and native client sql server driver just has more problems
//            if (isset($filters['startdate']) && $filters['startdate'] > 0){
//                $sql .= " AND DATEDIFF(s, '19700101', [Date]) >= ? ";
//                $params[] = $filters['startdate'];
//            }
            
            $sql .= " ORDER BY [Result_Rank] ASC";
        
        }
                    
        
        
        $query = $this->connection->query($sql, $params);
        
        if ($dateFilter){
            
            $result = false;
            $results = $this->connection->fetchAll($query);
            if ($results){
                
                foreach($results as $res){
                    
                    if ($res['ts'] >= $filters['startdate']){
                        $result = (object)$res;
                        break;
                    }
                    
                }
                
            }
            
            
        } else {
            
            $result = $this->connection->fetch($query);
            if ($result){
                $result = (object)$result;
            }
            
        }
                
        return $result;
        
    }
    
    
    
    /**
     * Average the latest ICT initial results for this student and assessmentid
     * @param type $username
     * @param type $assessmentID
     * @return type
     */
    public function getUserLatestICTIA($username, $assessmentID, $filters = false){
        
        $params = array($username, $assessmentID);
                
        $dateFilter = (isset($filters['startdate']) && $filters['startdate'] > 0) ? true : false;
        
        // If comparing dates, we need to get them all then loop through and find the first one that
        // meets that date comparison. Otherwise just get first 1
        $top = ($dateFilter) ? "" : "TOP 1";
        
        $sql = "SELECT {$top} [SessionID], CAST( [Date] as char(20) ) as 'Date',
                             CASE CONVERT(int, ROUND(AVG(CONVERT(decimal, [Result_Rank])), 0))
                                WHEN 1 THEN 'ICT Level 2'
                                WHEN 2 THEN 'ICT Level 1'
                                WHEN 3 THEN 'ICT Entry 3'
                                WHEN 4 THEN 'ICT Below Entry 3'
                            END as 'Result',
                            DATEDIFF(s, '19700101', [Date]) as ts
                FROM [bksb_InitialAssessmentResults]
                WHERE [userName] LIKE ? AND [Assessment_ID] LIKE ? ";
        
        // Can't get date comparisons to work with linux freetds driver, and native client sql server driver just has more problems
//        if (isset($filters['startdate']) && $filters['startdate'] > 0){
//            $sql .= " AND DATEDIFF(s, '19700101', [Date]) >= ? ";
//            $params[] = $filters['startdate'];
//        }
        
        $sql .= "GROUP BY [SessionID], [Date]
                ORDER BY [Date] DESC";
        
        
        $query = $this->connection->query($sql, $params);
        
        if ($dateFilter){
            
            $result = false;
            $results = $this->connection->fetchAll($query);
            if ($results){
                
                foreach($results as $res){
                    
                    if ($res['ts'] >= $filters['startdate']){
                        $result = (object)$res;
                        break;
                    }
                    
                }
                
            }
            
            
        } else {
            
            $result = $this->connection->fetch($query);
            if ($result){
                $result = (object)$result;
            }
            
        }
                
        return $result;
        
    }
    
    
    /**
     * Average the best ICT initial results for this student and assessmentid
     * @param type $username
     * @param type $assessmentID
     * @return type
     */
    public function getUserBestICTIA($username, $assessmentID, $filters = false){
        
        $dateFilter = (isset($filters['startdate']) && $filters['startdate'] > 0) ? true : false;
        
        // If comparing dates, we need to get them all then loop through and find the first one that
        // meets that date comparison. Otherwise just get first 1
        $top = ($dateFilter) ? "" : "TOP 1";
        
        
        $params = array($username, $assessmentID);
        
        $sql = "SELECT {$top} [SessionID], CAST( [Date] as char(20) ) as 'Date',
                             CASE CONVERT(int, ROUND(AVG(CONVERT(decimal, [Result_Rank])), 0))
                                WHEN 1 THEN 'ICT Level 2'
                                WHEN 2 THEN 'ICT Level 1'
                                WHEN 3 THEN 'ICT Entry 3'
                                WHEN 4 THEN 'ICT Below Entry 3'
                             END as 'Result',
                             CONVERT(int, ROUND(AVG(CONVERT(decimal, [Result_Rank])), 0)) as 'Rank',
                             DATEDIFF(s, '19700101', [Date]) as ts
                FROM [bksb_InitialAssessmentResults]
                WHERE [userName] LIKE ? AND [Assessment_ID] LIKE ? ";
        
        // Can't get date comparisons to work with linux freetds driver, and native client sql server driver just has more problems
//        if (isset($filters['startdate']) && $filters['startdate'] > 0){
//                $sql .= " AND DATEDIFF(s, '19700101', [Date]) >= ? ";
//                $params[] = $filters['startdate'];
//            }
        
        $sql .= " GROUP BY [SessionID], [Date]
                ORDER BY CONVERT(int, ROUND(AVG(CONVERT(decimal, [Result_Rank])), 0)) ASC";
                
        
        
        $query = $this->connection->query($sql, $params);
        
        if ($dateFilter){
            
            $result = false;
            $results = $this->connection->fetchAll($query);
            if ($results){
                
                foreach($results as $res){
                    
                    if ($res['ts'] >= $filters['startdate']){
                        $result = (object)$res;
                        break;
                    }
                    
                }
                
            }
            
            
        } else {
            
            $result = $this->connection->fetch($query);
            if ($result){
                $result = (object)$result;
            }
            
        }
        
        return $result;
        
    }
    
    
 
    
    /**
     * Get all the diagnostic results for this user
     * @param type $username
     */
    public function getUserAllDiag($username){
        
        $sql = "SELECT [Session_ID], [userName], [Percent_Score] as 'Result', 
                CAST( [Date] as char(20) ) as 'Date', [Diag_DisplayShort] as 'Subject'
                FROM [bksb_DiagnosticAssessmentResults]
                WHERE [userName] LIKE ? 
                ORDER BY [Date] DESC";
        
        $results = $this->connection->query($sql, array($username));
        return $this->connection->fetchAll($results);
        
    }
    
    
    
    /**
     * Get all the diagnostic results for this user
     * @param type $username
     */
    public function getUserBestDiag($username, $subject){
        
        $bksbSubject = $subject . " Diagnostic";
        $bksbSubjectOld = $subject . " Diag";
        
        $sql = "SELECT TOP 1 [Session_ID], [userName], [Percent_Score] as 'Result', 
                CAST( [Date] as char(20) ) as 'Date', [Diag_DisplayShort] as 'Subject'
                FROM [bksb_DiagnosticAssessmentResults]
                WHERE [userName] LIKE ? AND [Diag_DisplayShort] LIKE ?
                ORDER BY [Percent_Score] DESC";
        
        $result = $this->connection->query($sql, array($username, $bksbSubject));
        $result = $this->connection->fetch($result);
        
        if ($result)
        {
            $result = (object)$result;
            return $result;    
        }
        else
        {
            
            // Try old naming convention
            $sql = "SELECT TOP 1 [Session_ID], [userName], [Percent_Score] as 'Result', 
                CAST( [Date] as char(20) ) as 'Date', [Diag_DisplayShort] as 'Subject'
                FROM [bksb_DiagnosticAssessmentResults]
                WHERE [userName] LIKE ? AND [Diag_DisplayShort] LIKE ?
                ORDER BY [Percent_Score] DESC";
        
            $result = $this->connection->query($sql, array($username, $bksbSubjectOld));
            $result = $this->connection->fetch($result);
            if ($result)
            {
                $result = (object)$result;
                return $result;    
            }

        }
        
        return false;
            
    }
    
    
    
    
    
}
