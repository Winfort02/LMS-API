<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Electical Supply</title>

</head>
<style>
    body {
      font-family: Arial, Helvetica, sans-serif;
    }
    table {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse !important;
      width: 100%;
    }
    td, th {
      border: 1px solid #ddd;
      text-align: left;
      padding: 4px;
      font-size: 12px;
    }

    /* tbody > tr:nth-child(even) {
      background-color: #0E5E6F;
      color: white;
    } */

    .tr-total {
      background-color: #0E5E6F !important;
      color: white;
    }

    .remove-border {
      border: none !important;
    }
    .remove-border-left {
      border-left: none !important;
    }
    .remove-border-right {
      border-right: none !important;
    }
    .remove-border-top {
      border-top: none !important;
    }
    .remove-border-bottom {
      border-bottom: none !important;
    }

    .border-right {
      border-left: none !important;
      border-right: 1px solid #ddd;
    }

    .border-left {
      border-left: 1px solid #ddd;
      border-right: none !important;
    }

    .uppercase {
      text-transform: uppercase;
    }

    .capitalize {
      text-transform: capitalize;
    }

    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }

    .text-left {
      text-align: left;
    }

    .header-layout {
      display: inline-block;
      padding: 2rem;
    }
    .title {
      font-size: 16px;
      padding: 16rem;
      padding-left: 2rem;
    }

    .text-xs {
      font-size: 7px;
    }

    .text-sm {
      font-size: 8px;
    }
    .text-md {
      font-size: 11px;
    }
    .text-lg {
      font-size: 18px;
    }

    .text-white {
      color: #fff;
    }

    .text-success {
      color: #268367;
    }

    .text-danger {
      color: #ff0000;
    }

    .bg-secondary {
      backgroud-color: #FF5733;
    }

    @page { margin: 5px; }
    
  </style>
<body>
    @yield('content')
</body>
</html>
