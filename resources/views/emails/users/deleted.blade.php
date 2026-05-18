<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 10px 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            background-color: white;
            border: 1px solid #eee;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .details-table th, .details-table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>User Deletion Alert</h2>
        </div>
        <div class="content">
            <p><strong>Admin Action Notification</strong></p>
            <p>This is an automated alert to notify you that a user has been deleted from the system.</p>
            
            <p><strong>Action Performed By:</strong> {{ $adminName }}</p>
            <p><strong>Date & Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>

            <h3>Deleted User Details</h3>
            <table class="details-table">
                <tr>
                    <th>Name:</th>
                    <td>{{ $userData['name'] }}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{{ $userData['email'] }}</td>
                </tr>
                <tr>
                    <th>Employee Number:</th>
                    <td>{{ $userData['employee_number'] }}</td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $userData['role'])) }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>This email was intended for masiyatino9@gmail.com.</p>
            <p>ChronoSync Attendance System Automations</p>
        </div>
    </div>
</body>
</html>


