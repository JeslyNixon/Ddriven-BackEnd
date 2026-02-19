<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Properties List - {{ date('Y-m-d') }}</title>
    <style>
        @page {
        margin: 10mm 10mm 10mm 10mm;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
    }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
        }
        
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .meta-info div {
            font-size: 10px;
        }
        
        .meta-info strong {
            color: #2c3e50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #34495e;
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .project-id {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .status-completed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            padding: 10px;
            border-top: 1px solid #e0e0e0;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #2c3e50;
        }
        
        .summary h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .label {
            font-size: 9px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .summary-item .value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Properties List Report</h1>
        <p>Generated on {{ date('F d, Y \a\t h:i A') }}</p>
    </div>
    
    <!-- Meta Information -->
    <div class="meta-info">
        <div>
            <strong>Total Properties:</strong> {{ $properties->count() }}
        </div>
      <div>
            <strong>Status:</strong> {{ ucfirst($filteredStatus) }}
        </div>
    </div>
  
    <!-- Properties Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Project ID</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 18%;">Owner Name</th>
                <th style="width: 18%;">Bank</th>
                <th style="width: 30%;">Site Address</th>
                <th style="width: 12%;" class="text-center">Created Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($properties as $property)
                <tr>
                    <td class="project-id">{{ $property->project_id }}</td>
                    <td>
                         <span class="status-badge status-{{ str_replace(' ', '-', strtolower($property->propertyStatus->name ?? 'pending')) }}">
                            {{  $property->propertyStatus->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>{{ $property->owner_name }}</td>
                    <td>{{ $property->bank }}</td>
                    <td>{{ $property->site_address }}</td>
                    <td class="text-center">{{ $property->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 30px;">
                        No properties found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <p>Page <span class="page-number"></span> </p>
    </div>
</body>
</html>