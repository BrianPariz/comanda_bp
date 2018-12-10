<?php

$filename = "logs.xls";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

    $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
    $consulta = $objetoAccesoDatos->RetornarConsulta("select l.id, e.usuario as idEmpleado, l.fecha,l.accion FROM logs l LEFT JOIN empleados e on l.idEmpleado = e.id");
    $consulta->execute();
    $result = $consulta->fetchAll(PDO::FETCH_CLASS, "Log");
?>

<table>
    <tr>
        <th style="border: 1px solid black;background-color: green;">Id</th>
        <th style="border: 1px solid black;background-color: green;">Empleado</th>
        <th style="border: 1px solid black;background-color: green;">Fecha</th>
        <th style="border: 1px solid black;background-color: green;">Accion</th>
    </tr>
    <?php
    foreach($result as $row) {
        ?>
            <tr>
                <td style="border: 1px solid black;"><?php echo $row->id; ?></td>
                <td style="border: 1px solid black;"><?php echo $row->idEmpleado; ?></td>
                <td style="border: 1px solid black;"><?php echo $row->fecha; ?></td>
                <td style="border: 1px solid black;"><?php echo $row->accion; ?></td>
            </tr>
        <?php
    }
    ?>
</table>