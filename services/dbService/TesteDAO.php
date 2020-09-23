<?php
namespace dbService;
use \PDO;
use \Slim\Container;
class TesteDAO
{
    private $connector;
    function __construct(Container $c=null)
    {
        if($conn==null)
        {
            exit("conector is empty");
        }
        $this->connector = $c['ConectorDAO'];
    }
    public function list()
    {
        $sql = "
            SELECT
                    [NOME_CLIENTE]
            FROM [dbo].[TB_CLIENTES]
        ";
        $stmt = $this->connector->prepare($sql);
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new SqlCommandException($e);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }
    public function getListPartial($origem, $area, $status, $documento)
    {
        $sql = "
            SELECT
                    [cod]
                    ,[documento]
                    ,[numero]
                    ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                    ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                    ,[areaResponsavel]
                    ,[mandato]
                    ,[providencia]
                    ,[status]
                    ,[validacao]
                    ,[origem]
            FROM [dbo].[demandas]
                where [status] not in ('FINALIZADO NP','FINALIZADO AP') 
                and excluido is null
        ";
        if($origem != "Selecione")
        {
            $sql .= " and [origem] = :origem ";
        }
        if($area != "Selecione")
        {
            $sql .= " and [areaResponsavel] like :area ";
        }
        if($status != "Selecione")
        {
            $sql .= " and [status] = :status ";
        }
        if($documento != "Selecione")
        {
            $sql .= " and [documento] like :documento ";
        }
        $sql .=
        "
                order by prazoResposta asc
        ";
        $stmt = $this->connector->prepare($sql);
        if($origem != "Selecione")
        {
            $stmt->bindParam(":origem", $origem);
        }
        if($area != "Selecione")
        {
            $param = "%".$area."%";
            $stmt->bindParam(":area", $param,PDO::PARAM_STR);
        }
        if($status != "Selecione")
        {
            $stmt->bindParam(":status", $status);
        }
        if($documento != "Selecione")
        {
            $param = $documento."%";
            $stmt->bindParam(":documento", $param,PDO::PARAM_STR);
        }
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new SqlCommandException($e);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
//         foreach ($stmt->fetch(PDO::FETCH_ASSOC) as $row)
//         {
//             yield $row;
//         }
    }
    public function getList()
    {
        #$sql = "select * from [dbo].[DESTAQUES]";
        $sql = "
            SELECT
                    [cod]
                    ,[documento]
                    ,[numero]
                    ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                    ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                    ,[areaResponsavel]
                    ,[mandato]
                    ,[providencia]
                    ,[status]
                    ,[validacao]
                    ,[origem]
            FROM [dbo].[demandas]
                where [status] not in ('FINALIZADO NP','FINALIZADO AP') 
                and excluido is null
                order by prazoResposta asc
        ";
        foreach ($this->connector->query($sql, PDO::FETCH_ASSOC) as $row)
        {
            yield $row;
        }
    }
    public function getListSuperintendencia($codSN= null)
    {
        if($codSN==null)
        {
            throw new \Exception("bad method call.");
        }
        $sql = "
            SELECT
                    [cod]
                    ,[documento]
                    ,[numero]
                    ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                    ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                    ,[areaResponsavel]
                    ,[mandato]
                    ,[providencia]
                    ,[status]
                    ,[validacao]
                    ,[origem]
            FROM [dbo].[demandas]
                where ([demandas].[status] not in ('FINALIZADO NP','FINALIZADO AP')) 
                and (excluido is null)
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $sql.= " order by prazoResposta asc ";
        #die(var_dump($sql));
        foreach ($this->connector->query($sql, PDO::FETCH_ASSOC) as $row)
        {
            yield $row;
        }
    }
    private function getHistoricoAvaliacao($cod)
    {
        $html = NULL;
        $sql = "
            select
                cod
            	,fkAcoes
            	,convert(varchar(10),dataAvaliacao,103) as dataAvaliacao
            	,avaliacao
            	,matricula
            	,observacao
            from
                dbo.historicoAvaliacaoAcao
            where
                fkAcoes = :cod
            order by
                cod desc
        ";
        $stmt = $this->connector->prepare($sql);
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function delete($cod=null){
        if($cod == null){
            return;
        }
        #exclui resposta
        $stmt = $this->connector->prepare("update dbo.respostas set excluido = 1 where fkDemandas=:cod");
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        #exclui acao
        $stmt = $this->connector->prepare("update dbo.acoes set excluido = 1 where fkDemandas=:cod");
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        #exclui fragilidade recomendacao
        $stmt = $this->connector->prepare("update dbo.fragilidadesRecomendacoes set excluido = 1 where fkDemandas=:cod");
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        #exclui demanda
        $stmt = $this->connector->prepare("update dbo.demandas set excluido = 1 where cod=:cod");
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
    }
    public function insertDemanda($demanda=null){
        if($demanda==null || !is_array($demanda)){
            throw new \Exception("To insert a demanda its necessary to info one.");
        }
        $sqlCommand = "
            INSERT INTO [dbo].[demandas]
                        (
                            documento
                        	,numero
                        	,dataDocumento
                        	,prazoResposta
                        	,areaResponsavel
                        	,mandato
                        	,providencia
                        	,status
                        	,validacao
                            ,dataFinalizacao
                            ,obs
                            ,origem
                        )
            VALUES
                        (
                             :documento
                        	,:numero
                        	,:dataDocumento
                        	,:prazoResposta
                        	,:areaResponsavel
                        	,:mandato
                        	,:providencia
                        	,:status
                        	,:validacao
                            ,:dataFinalizacao
                            ,:obs
                            ,:origem
                        )

        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":documento", $demanda["documento"]);
        $stmt->bindParam(":numero", $demanda["numero"]);
        $stmt->bindParam(":dataDocumento", $demanda["dataDocumento"]);
        $stmt->bindParam(":prazoResposta", $demanda["prazoResposta"]);
        $stmt->bindParam(":areaResponsavel", $demanda["areaResponsavel"]);
        $stmt->bindParam(":mandato", $demanda["mandato"]);
        $stmt->bindParam(":providencia", $demanda["providencia"]);
        $stmt->bindParam(":status", $demanda["status"]);
        $stmt->bindParam(":validacao", $demanda["validacao"]);
        $stmt->bindParam(":dataFinalizacao", $demanda["dataFinalizacao"]);
        $stmt->bindParam(":obs", $demanda["obs"]);
        $stmt->bindParam(":origem", $demanda["origem"]);
        $stmt->execute();
        return $this->connector->lastInsertId();
        /*
        $sqlCommand = "select @@IDENTITY as id";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)["id"];
        */
    }
    public function getDemanda($codDemanda=null){
        if($codDemanda == null || !is_numeric($codDemanda)){
            throw new \Exception("cod from Demanda not informed.");
        }
        $sqlCommand = "
            SELECT
            	   [cod]
                  ,[documento]
                  ,[numero]
                  ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                  ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                  ,[areaResponsavel]
                  ,[mandato]
                  ,[providencia]
                  ,[status]
                  ,[validacao]
                  ,[origem]
                  ,case 
                        when dataFinalizacao is not null then convert(varchar(10),dataFinalizacao,103) 
                        else null
                   end as dataFinalizacao
                  ,obs
            FROM [dbo].[demandas]
            where
                cod=:cod and excluido is null
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":cod",$codDemanda);
        $stmt->execute();
        //return $stmt->fetch(PDO::FETCH_ASSOC);
        $demanda = $stmt->fetch(PDO::FETCH_ASSOC);
        $sqlCommand = "
            SELECT [cod]
                  ,[fragilidades]
                  ,[recomendacoes]
                  ,[fkDemandas]
              FROM [dbo].[fragilidadesRecomendacoes]
              where fkDemandas = :fkDemandas
              and excluido is null
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":fkDemandas", $codDemanda);
        $stmt->execute();
        $demanda["recomendacoes"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sqlCommand = "
            SELECT [cod]
              ,[fragilidades]
              ,ltrim(rtrim(replace(replace([recomendacoes], char(13), ''), char(10), ''))) as recomendacoes
              ,[area]
              ,[acao]
              ,convert(varchar(10),[prazoProposto],103) as prazoProposto
              ,[status]
              ,[fkDemandas]
              ,[etapa]
                  ,case
                    when dataFinalizacao is not null then convert(varchar(10), dataFinalizacao,103)
                    else null
                   end as dataFinalizacao
                  ,areaValidacao
                  ,justificativa
          FROM [dbo].[acoes]
              where fkDemandas = :fkDemandas and excluido is null
              order by recomendacoes, fragilidades, acao, etapa, prazoProposto asc
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":fkDemandas", $codDemanda);
        $stmt->execute();
        $demanda["acoes"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrayAcoes = array();
        foreach($demanda["acoes"] as $acao)
        {
            $acao["historicoAvaliacao"] = $this->getHistoricoAvaliacao($acao["cod"]);
            array_push($arrayAcoes, $acao);
        }
        $demanda["acoes"] = $arrayAcoes;
        #die(var_dump($demanda["acoes"]));
        $sqlCommand = "
            select 
                cod
                ,area
                ,acao
                ,convert(varchar(10),prazoProposto,103) as prazoProposto
                ,etapa
                ,[status]
            from
                dbo.respostas
            where
                fkDemandas = :fkDemandas
                and excluido is null
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":fkDemandas", $codDemanda);
        $stmt->execute();
        $demanda["respostas"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrayRespostas = array();
        foreach ($demanda["respostas"] as $resposta)
        {
            $resposta["historicoAvaliacao"] = $this->getHitoricoAvalResposta($resposta["cod"]);
            array_push($arrayRespostas,$resposta);
        }
        $demanda["respostas"] = $arrayRespostas;
        return $demanda;
    }
    private function getHitoricoAvalResposta($cod)
    {
        $sqlCommand = "
            SELECT [cod]
                  ,[fkRespostas]
                  ,[dataAvaliacao]
                  ,[avaliacao]
                  ,[matricula]
                  ,[observacao]
              FROM [dbo].[historicoAvaliacaoResposta]
            where [fkRespostas] = :cod
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateDemanda($demanda=null){
        if($demanda==null || !is_array($demanda)){
                throw new \Exception("To insert a demanda its necessary to info one.");
        }
        $sqlCommand = "
        UPDATE [dbo].[demandas]
           SET [documento] = :documento
              ,[numero] = :numero
              ,[dataDocumento] = :dataDocumento
              ,[prazoResposta] = :prazoResposta
              ,[areaResponsavel] = :areaResponsavel
              ,[mandato] = :mandato
              ,[providencia] = :providencia
              ,[status] = :status
              ,[validacao] = :validacao
              ,[dataFinalizacao] = :dataFinalizacao
              ,[obs] = :obs
              ,[origem]=:origem
           WHERE
                cod = :cod
    ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":cod", $demanda["cod"]);
        $stmt->bindParam(":documento", $demanda["documento"]);
        $stmt->bindParam(":numero", $demanda["numero"]);
        $stmt->bindParam(":dataDocumento", $demanda["dataDocumento"]);
        $stmt->bindParam(":prazoResposta", $demanda["prazoResposta"]);
        $stmt->bindParam(":areaResponsavel", $demanda["areaResponsavel"]);
        $stmt->bindParam(":mandato", $demanda["mandato"]);
        $stmt->bindParam(":providencia", $demanda["providencia"]);
        $stmt->bindParam(":status", $demanda["status"]);
        $stmt->bindParam(":validacao", $demanda["validacao"]);
        $stmt->bindParam(":dataFinalizacao", $demanda["dataFinalizacao"]);
        $stmt->bindParam(":obs", $demanda["obs"]);
        $stmt->bindParam(":origem", $demanda["origem"]);
        $stmt->execute();
    }
    public function getQuadroAcoes($cod = null)
    {
        if($cod == null) 
        {
            throw new \Exception("bad method call.");
        }
        $sqlCommand = "
        select 
        	demandas.documento + ' - ' + numero  as documento
        	,replace(demandas.mandato,' ', '<br>') as mandato 
        	,acoes.fragilidades
        	,acoes.recomendacoes
        	,acoes.area
            ,acoes.acao
        	,acoes.etapa
        	,case 
        		when acoes.prazoProposto is not null then convert(varchar(10), acoes.prazoProposto, 103)
        		else null
        	 end as prazoProposto
        	,acoes.[status]  
        from 
        	dbo.demandas as demandas
        	left outer join dbo.acoes  as acoes
        		on demandas.cod = acoes.fkDemandas
        where
        	demandas.cod = :cod and acoes.excluido is null
        order by recomendacoes, fragilidades, acao, etapa, prazoProposto asc
    ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getQuadroRespostas($cod = null)
    {
        if($cod == null)
        {
            throw new \Exception("bad method call.");
        }
        $sqlCommand = "
        select 
        	demandas.documento + ' - ' + numero  as documento
        	,replace(demandas.mandato,' ', '<br>') as mandato 
        	,respostas.area
        	,respostas.acao
        	,respostas.etapa
        	,case 
        		when respostas.prazoProposto is not null then convert(varchar(10), respostas.prazoProposto, 103) 
        		else null
        	 end as prazoProposto 
        	,respostas.[status]
        from 
        	dbo.demandas as demandas
        	left outer join dbo.respostas  as respostas
        		on demandas.cod = respostas.fkDemandas
        where
        	demandas.cod = :cod and respostas.excluido is null
        order by  acao, etapa, prazoProposto, area asc
        ";
        $stmt = $this->connector->prepare($sqlCommand);
        $stmt->bindParam(":cod", $cod);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getListFinalizado()
    {
        $sql = "
            SELECT
                    [cod]
                    ,[documento]
                    ,[numero]
                    ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                    ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                    ,[areaResponsavel]
                    ,[mandato]
                    ,[providencia]
                    ,[status]
                    ,[validacao]
                    ,[origem]
            FROM [dbo].[demandas]
                where [status]  in ('FINALIZADO NP','FINALIZADO AP') 
                    and excluido is null
                order by prazoResposta desc
        ";
        foreach ($this->connector->query($sql, PDO::FETCH_ASSOC) as $row)
        {
            yield $row;
        }
    }
    public function getListFinalizadoSuperintendencia($codSN= null)
    {
        $sql = "
            SELECT
                    [cod]
                    ,[documento]
                    ,[numero]
                    ,CONVERT(varchar(10),[dataDocumento],103) as [dataDocumento]
                    ,CONVERT(varchar(10),[prazoResposta],103) as [prazoResposta]
                    ,[areaResponsavel]
                    ,[mandato]
                    ,[providencia]
                    ,[status]
                    ,[validacao]
                    ,[origem]
            FROM [dbo].[demandas]
                where  
                     [status]  in ('FINALIZADO NP','FINALIZADO AP') 
                    and (excluido is null)
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $sql.=" order by prazoResposta desc ";
        foreach ($this->connector->query($sql, PDO::FETCH_ASSOC) as $row)
        {
            yield $row;
        }
    }
    public function getOrigemConsolidado()
    {
        $sql = "
            SELECT
                distinct 
                [origem]
                FROM [dbo].[demandas]
                where [status] not in ('FINALIZADO NP','FINALIZADO AP') 
                and excluido is null
        ";
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOrigemConsolidadoSuperintendencia($codSN= null)
    {
        $sql = "
            SELECT distinct 
                    [origem]
            FROM [dbo].[demandas]
                where [status] not in ('FINALIZADO NP','FINALIZADO AP') 
                and (excluido is null)
            
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOrigemFinalizado()
    {
        $sql = "
            SELECT distinct 
                    [origem]
            FROM [dbo].[demandas]
            where [status]  in ('FINALIZADO NP','FINALIZADO AP')
                and excluido is null
        ";
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOrigemFinalizadoSuperintendencia($codSN= null)
    {
        $sql = "
            SELECT distinct 
                    [origem]
            FROM [dbo].[demandas]
                where
                     [status]  in ('FINALIZADO NP','FINALIZADO AP')
                    and (excluido is null)
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAreaResponsavelConsolidado()
    {
        $sql = "
            SELECT
                distinct
                [areaResponsavel]
                FROM [dbo].[demandas]
                where status not in ('FINALIZADO NP','FINALIZADO AP')
                and excluido is null
        ";
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        $listaAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listaAreasProcessada = array();
        foreach($listaAreas as $area)
        {
            if(strpos($area["areaResponsavel"],"|"))
            {
                foreach(explode("|", $area["areaResponsavel"]) as $areaProcessada)
                {
                    array_push($listaAreasProcessada, $areaProcessada);
                }
            }
            else 
                array_push($listaAreasProcessada, $area["areaResponsavel"]);
        }
        return array_unique($listaAreasProcessada);
    }
    public function getAreaResponsavelConsolidadoSuperintendencia($codSN= null)
    {
        $sql = "
            SELECT distinct
                    [areaResponsavel]
            FROM [dbo].[demandas]
                where [status] not in ('FINALIZADO NP','FINALIZADO AP')
                and (excluido is null)
            
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        $listaAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listaAreasProcessada = array();
        foreach($listaAreas as $area)
        {
            if(strpos($area["areaResponsavel"],"|"))
            {
                foreach(explode("|", $area["areaResponsavel"]) as $areaProcessada)
                {
                    array_push($listaAreasProcessada, $areaProcessada);
                }
            }
            else
                array_push($listaAreasProcessada, $area["areaResponsavel"]);
        }
        return array_unique($listaAreasProcessada);
    }
    public function getAreaResponsavelFinalizado()
    {
        $sql = "
            SELECT distinct
                    [areaResponsavel]
            FROM [dbo].[demandas]
            where [status]  in ('FINALIZADO NP','FINALIZADO AP')
                and excluido is null
        ";
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        $listaAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listaAreasProcessada = array();
        foreach($listaAreas as $area)
        {
            if(strpos($area["areaResponsavel"],"|"))
            {
                foreach(explode("|", $area["areaResponsavel"]) as $areaProcessada)
                {
                    array_push($listaAreasProcessada, $areaProcessada);
                }
            }
            else
                array_push($listaAreasProcessada, $area["areaResponsavel"]);
        }
        return array_unique($listaAreasProcessada);
    }
    public function getAreaResponsavelFinalizadoSuperintendencia($codSN= null)
    {
        $sql = "
            SELECT distinct
                    [areaResponsavel]
            FROM [dbo].[demandas]
                where
                     [status]  in ('FINALIZADO NP','FINALIZADO AP')
                    and (excluido is null)
        ";
        switch($codSN)
        {
            case "5046":
                $sql.= " and ([areaResponsavel] like '%SUPOC%' or [areaResponsavel] like '%GEPOD%' OR [areaResponsavel] like '%GEPOC%') ";
                break;
            case "5194":
                $sql.= " and ([areaResponsavel] like '%SUCNI%' or [areaResponsavel] like '%GEESC%' OR [areaResponsavel] like '%GERIN%') ";
                break;
            case "5795":
                $sql.= " and ([areaResponsavel] like '%SUSEB%' or [areaResponsavel] like '%GESEB%') ";
                break;
            case "5073":
                $sql.= " and ([areaResponsavel] like '%SUECO%') ";
        }
        $stmt = $this->connector->prepare($sql);
        $stmt->execute();
        $listaAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listaAreasProcessada = array();
        foreach($listaAreas as $area)
        {
            if(strpos($area["areaResponsavel"],"|"))
            {
                foreach(explode("|", $area["areaResponsavel"]) as $areaProcessada)
                {
                    array_push($listaAreasProcessada, $areaProcessada);
                }
            }
            else
                array_push($listaAreasProcessada, $area["areaResponsavel"]);
        }
        return array_unique($listaAreasProcessada);
    }
}
?>
