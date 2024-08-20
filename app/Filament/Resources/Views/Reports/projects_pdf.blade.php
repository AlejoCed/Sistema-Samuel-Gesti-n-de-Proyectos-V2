<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Proyectos</title>
</head>
<body>
    <h1>Reporte de Proyectos</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Presupuesto</th>
                <th>Fecha de Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
            <tr>
                <td>{{ $project->id }}</td>
                <td>{{ $project->nombre }}</td>
                <td>{{ $project->status }}</td>
                <td>{{ $project->budget }}</td>
                <td>{{ $project->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
