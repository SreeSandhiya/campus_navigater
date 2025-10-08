const places = {
  "Engineering Blocks": {
    type: "Academic",
    description: "Includes IT, CSE, ECE, and Mechanical departments.",
    facilities: ["Computer Labs", "Smart Classrooms", "Project Rooms", "Faculty Offices"],
    contact: "engg@college.edu"
  },
  "AI & Data Science": {
    type: "Academic",
    description: "Dedicated block for Artificial Intelligence and Data Science.",
    facilities: ["AI Lab", "Data Center", "Machine Learning Lab", "Research Center"],
    contact: "aids@college.edu"
  },
  "Biotech & Biomedical": {
    type: "Academic",
    description: "Biotechnology and Biomedical department block.",
    facilities: ["Bio Lab", "Research Center", "Medical Lab"],
    contact: "bio@college.edu"
  },
  "VSB Administration": {
    type: "Administrative",
    description: "Main administrative building of the campus.",
    facilities: ["Principal Office", "Admission Office", "Conference Hall"],
    contact: "admin@college.edu"
  },
  "Boys Hostel": {
    type: "Residential",
    description: "Modern accommodation for male students.",
    facilities: ["Dormitory Rooms", "Recreation Room", "Study Area", "Mess Hall", "Gym"],
    contact: "boys-hostel@college.edu"
  },
  "Girls Hostel": {
    type: "Residential",
    description: "Modern accommodation for female students.",
    facilities: ["Dormitory Rooms", "Recreation Room", "Study Area", "Mess Hall", "Gym"],
    contact: "girls-hostel@college.edu"
  },
  "Temple": {
    type: "Religious",
    description: "Spiritual center of the campus.",
    facilities: ["Meditation Hall", "Prayer Area", "Cultural Events"],
    contact: "temple@college.edu"
  },
  "Sports Ground": {
    type: "Amenity",
    description: "Outdoor ground for sports and physical activities.",
    facilities: ["Football Field", "Cricket Ground", "Athletics Track"],
    contact: "sports@college.edu"
  },
  "Bus Stand": {
    type: "Transport",
    description: "Campus bus and transport facility.",
    facilities: ["Bus Parking", "Student Pickup", "Shuttle Service"],
    contact: "transport@college.edu"
  }
};

document.addEventListener("DOMContentLoaded", function () {
  const dots = document.querySelectorAll(".dot");
  const placeTitle = document.getElementById("place-title");
  const placeType = document.getElementById("place-type");
  const placeDesc = document.getElementById("place-desc");
  const placeFacilities = document.getElementById("place-facilities");
  const placeContact = document.getElementById("place-contact");

  function showDetails(place) {
    const data = places[place];
    placeTitle.textContent = place;
    placeType.textContent = data.type;
    placeDesc.textContent = data.description;
    placeFacilities.innerHTML = data.facilities.map(f => `<li>${f}</li>`).join("");
    placeContact.textContent = data.contact;
  }

  dots.forEach(dot => {
    dot.addEventListener("click", function () {
      const place = this.getAttribute("data-place");
      showDetails(place);
    });
  });

  // ✅ Default load: show Temple details
  showDetails("Temple");
});
