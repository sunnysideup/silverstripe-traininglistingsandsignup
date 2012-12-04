<% control MonthlyCourses %>
<div class="MonthlyCoursesMonth">
	$Date.Format(F) - $Date.Year
	<ul>
	<% control Courses  %>
		<li>
			<div class="TrainingItemRowOne">
				<span class="TrainingTitle">$Title</span>
				<span class="TrainingEnrolNow"><a href="$Link">Enrol Now</a></span>
			</div>
			<div class="TrainingItemRowTwo">
				<span class="TrainingDate">$Date.DayOfMonth<% if DifferentEndMonth %> $Date.Format(F)<% end_if %><% if DifferentEndDate %> - $EndDate.DayOfMonth $EndDate.Format(F)</span><% else %> $Date.Format(F)<% end_if %>
				| <span class="TrainingLocation">$Location</span>
				| <span class="TrainingPrice">$Price.Nice</span>
			</div>
			<div class="TrainingItemRowThree">
				$Content
			</div>
		</li>
	<% end_control %>
	</ul>
</div>
<% end_control %>





