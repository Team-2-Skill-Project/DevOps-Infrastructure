import React from 'react'

export default function LoginHeading({title, subTitle}) {
  return (
    <div>
        <h1 className='text-[28px] font-bold text-[#0f172a] mb-2'>{title}</h1>
        <p className='text-[14px] text-[#64748b] mb-7.5'>{subTitle}</p>
    </div>
  )
}
