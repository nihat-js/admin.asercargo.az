<meta name="csrf-token" content="{{ csrf_token() }}">


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuetify/2.0.15/vuetify.min.css"> -->

<style>
  header {
    background-color: #10458c;
  }
</style>

<header id="header">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center py-3">
      <div class="logo">
        <h2 class="text-primary">Aser Express</h2>
      </div>

      <div class="user">
        <h2 class="text-secondary">Dev.</h2>
      </div>

      <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary"> Change Status </button>

        <a href="http://localhost:8001/warehouse/distributor/detained-at-customs" class="btn btn-primary">
          Detained at Customs
        </a>

        <a href="http://localhost:8001/warehouse/distributor/change-branch" class="btn btn-success">
          Change Package Branch
        </a>
      </div>

      <div class="logout">
        <a href="http://localhost:8001/logout" class="text-primary text-decoration-none d-flex align-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24">
            <path fill="currentColor"
              d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z">
            </path>
          </svg>
          <p class="mb-0 ms-2">Log Out</p>
        </a>
      </div>

    </div>

  </div>
  </div>
</header>



<div class="container mt-5">

  <div class="d-flex mb-5">
    <button style="margin-right:10px" class="btn btn-secondary" onclick="window.history.back();">Go Back</button>
    <h2 class="ml-3">Branch Change Form </h2>
  </div>


  <form action="/submit" method="POST">
    <div class="mb-3">
      <input type="text" class="form-control" id="scanInput" name="scanInput" placeholder="Scan QR or Barcode" required>
    </div>

    <!-- Branch Select Dropdown -->
    <div class="mb-3">
      <label for="branchSelect" class="form-label">Select Branch</label>
      <select class="form-select" name="branch_id" id="branch_id" required oninput="">
        <!-- <option value="">Seçin</option> -->

        @foreach ($branches as $branch)
      <option value="{{$branch->id}}"> {{$branch->name}} </option>
    @endforeach
      </select>
    </div>

    <!-- Submit Button -->
    <!-- <button type="submit" class="btn btn-primary">Submit</button> -->
  </form>
</div>

<script>
  $(document).ready(function () {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    $('#scanInput').on('keypress', function (event) {
      if (event.which == 13) { // 13 is the Enter key
        event.preventDefault(); // Prevent default Enter key behavior (like submitting the form normally)

        // Send AJAX request to submit the form data
        sendFormData();
      }
    });

    // Send data via AJAX
    function sendFormData() {
      // Get the form data
      var formData = {
        package: $('#scanInput').val(),
        branch_id: $('#branch_id').val()
      };

      // Send the AJAX request
      $.ajax({
        url: '', // Change to the URL where you want to send the form data
        method: 'POST',
        data: formData,
        success: function (response) {
          alert(response.content)
          // alert('Form submitted successfully!');
          // You can perform any action on success, like clearing the form or redirecting
        },
        error: function (error) {
          alert('An error occurred. Please try again!');
        }
      });
    }
  });
</script>